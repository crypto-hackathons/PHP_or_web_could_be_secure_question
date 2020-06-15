<?php 

trait Id_simple {
    
    use Hash_simple, Compress_simple, Rsa_simple, Crypto_simple;
    
    public static $id_dir;
    public static $id_telNumber;
    public static $id_word;
    public static $id_emailAddress;
    public static $id_commonName;
    public static $id_password;
    public static $id_pgp_passphrase;
    public static $id_name;
    public static $id_anon;
    
    public function id_init(
        string $n,
        string $countryName,
        string $stateOrProvinceName,
        string $localityName,
        string $organizationName,
        string $organizationalUnitName,
        string $commonName,
        string $emailAddress,
        string $telNumber,
        string $password,
        string $pgp_passphrase,
        string $id_lang,
        string $id_timezone,
        string $wordlist_file = self::$SEED_DEFAULT_WORDLIST_FILE,
        bool $crypt_pgp_state = false,
        array $conf = array()) {
                    
        $dn = './';
        self::hash_init();        
        self::$id_name = $n;
        $name_hashed =  self::hash($n);
        self::$id_dir = self::id_dir_create_all($name_hashed , $dn);
        self::$rsa_public_key_file = self::$id_dir->key_dir.'/private.pem';
        self::$rsa_private_key_file = self::$id_dir->key_dir.'/public.pem';
        self::$cert_csr_file = self::$id_dir->cert_dir.'/src.pem';
        self::$cert_x509_file = self::$id_dir->cert_dir.'/x509.pem';
        self::$cert_pkey_file = self::$id_dir->cert_dir.'/private_pwd.pem';
        self::$pgp_env = self::$id_dir->pgp_dir.'/.gnupg';
        self::$pgp_passphrase_file = self::$id_dir->pgp_dir.'/passphrase.pgp';
        self::$seed_private_key_master_dir = self::$id_dir->seed_dir.'/';
        self::$seed_grain_file = self::$id_dir->seed_dir.'/grain.txt';
        self::$crypt_pgp_state = $crypt_pgp_state;        
        self::rsa_init();
        self::cert_init(self::hash($countryName), self::hash($stateOrProvinceName), self::hash($localityName), self::hash($organizationName), self::hash($organizationalUnitName), self::hash($commonName), self::hash($emailAddress), self::hash($password));        
        self::pgp_init(self::hash($pgp_passphrase));
        self::seed_init($wordlist_file);
        self::$id_emailAddress = $emailAddress;
        self::$id_telNumber = $telNumber;
        self::$id_lang = $id_lang;
        self::$id_timezone = $id_timezone;
        self::$id_word = self::hash_array(json_decode(file_get_contents('../data/wordlists/'.$id_lang.'.json')));
        self::$id_public_key = self::rsa_public_key_get();
        self::$id_private_key = self::rsa_private_key_get();
        self::$id_commonName = $commonName;
        self::$id_password = self::hash($password);
        self::$id_pgp_passphrase = self::hash($pgp_passphrase);
                
        $conf['id_lang'] = self::$id_lang;
        $conf['id_timezone '] = self::$id_timezone;
        $conf['id_word'] = self::$id_word;
        $conf['id_commonName'] = self::$id_commonName;
        $conf['id_name'] = self::$id_name;
        $conf['id_emailAddress'] = hash(self::$id_emailAddress);
        $conf['id_telNumber'] = hash(self::$id_telNumber);
        
        $id = new stdClass();
        $id->conf = $conf;
        $id->data = get_class_vars(get_class($this));
        $id->cert = new stdClass();
        $id->cert->time = time();
        $id->cert->public_key = self::$id_public_key;
        $id->cert->priv_key_crypted = self::crypto_crypt(self::$id_private_key);
        $id->data = self::rsa_crypt(json_encode($id->data), self::$id_public_key);
        
        $data = json_encode($id);
        $id->hash = self::hash($data);
        
        $data = json_encode($id);
        $data = self::compress($data);
        $data_checksum = md5($data);
        
        $id_hashed = self::hash($password.self::$id_name);
        
        file_put_contents(self::$id_dir->id_dir.'/'.$id_hashed.'_'.$data_checksum.'.json', $data);
        
        return $data;
    }
    
    public static function id_dir_create(string $dir, string $n, string $dn){
        
        $dir = $dn.'/../data/'.$dir.'/'.$n;
        
        if(is_dir($dir) === false) mkdir($dir, 777, true);
    }
    
    public static function id_dir_create_all(string $n, string $dn, array $dir_list = array('key', 'cert', 'pgp', 'seed', 'id')): void{
        
        $dir = new stdClass();
        
        foreach($dir_list as $k => $v) {
            $dir->$k = self::id_dir_create($v, $n, $dn);
        }
        return $dir;
    }
    
    public static function id_get_from_sesison_id(string $session_id, string $cypher_key){
            
        $mask = '../data/session/'.$session_id.'_*.txt';
        
        foreach(glob($mask) as $file) {
            $i = explode(';', file_get_contents($file));
            $session_id_real = $i[0];
            $id_name_hashed = $i[1];
        }
        
        $mask = '../data/id/'.$id_name_hashed.'/'.$session_id_real.'_*'.'.json';
        
        foreach(glob($mask) as $file) {
            
            $data_checksum = explode('_', $file)[1];
            $data_checksum = explode('.', $data_checksum)[0];
            $data = file_get_contents($file);
            
            if($data_checksum !== $data)  error('Bad session intregrity.');
            
            $data = self::uncompress($data);
            $data = json_decode($data);
            $data->cert->priv_key = self::crypto_uncrypt($data->cert->priv_key_crypted, $cypher_key);
            $data->data = self::rsa_uncrypt($data->data);
        }
        if($data === false) error('Session not found.');
        
        return $session_id;
    }
    
    public static function id_get(string $id_name, string $password, string $cypher_key){
        
        $session_id = self::hash($password.$id_name);
        
        file_put_contents('../data/session/'.$session_id.'_'.uniqid().'.txt', $session_id.';'.self::hash($id_name));
        
        return self::id_get_from_sesison_id($session_id, $cypher_key);
    }
    
    public static function id_session_init(array $conf, $node){
        
        if(isset($_GET['sessionCreate']) === false) {
            
            return self::id_session_create($conf);
        }
        elseif(isset($_GET['sessionlogin']) === false) {
            
            return self::id_session_login();
        }
        elseif(isset($_GET['session']) === false) {
        
           return $session_id = self::id_session_anon_create($conf, $node);
        }
        elseif(isset($_GET['session']) === true) {
            
            return $session_id = self::id_session_load();
        }
    }
    
    public static function id_session_create(array $conf){
        
        if(isset($_POST['n']) === true) $n = urldecode(strip_tags($_POST['n']));
        if(isset($_POST['countryName']) === true) $countryName = urldecode(strip_tags($_POST['countryName']));
        if(isset($_POST['stateOrProvinceName']) === true) $stateOrProvinceName = urldecode(strip_tags($_POST['stateOrProvinceName']));
        if(isset($_POST['localityName']) === true) $localityName = urldecode(strip_tags($_POST['localityName']));
        if(isset($_POST['organizationName']) === true) $organizationName = urldecode(strip_tags($_POST['organizationName']));
        if(isset($_POST['organizationalUnitName']) === true) $organizationalUnitName = urldecode(strip_tags($_POST['organizationalUnitName']));
        if(isset($_POST['commonName']) === true) $commonName = urldecode(strip_tags($_POST['commonName']));
        if(isset($_POST['emailAddress']) === true) $emailAddress = urldecode(strip_tags($_POST['emailAddress']));
        if(isset($_POST['telNumber']) === true) $telNumber = urldecode(strip_tags($_POST['telNumber']));
        if(isset($_POST['password']) === true) $password = urldecode(strip_tags($_POST['password']));
        if(isset($_POST['pgp_passphrase']) === true) $pgp_passphrase = urldecode(strip_tags($_POST['pgp_passphrase']));
        if(isset($_POST['id_lang']) === true) $id_lang = urldecode(strip_tags($_POST['id_lang']));
        if(isset($_POST['id_timezone']) === true) $id_timezone = urldecode(strip_tags($_POST['id_timezone']));
        if(isset($_POST['wordlist_file']) === true) $wordlist_file = urldecode(strip_tags($_POST['wordlist_file']));
        
        $data = self::id_init(
            $n,
            $countryName,
            $stateOrProvinceName,
            $localityName,
            $organizationName,
            $organizationalUnitName,
            $commonName,
            $emailAddress,
            $telNumber,
            $password,
            $pgp_passphrase,
            $id_lang,
            $id_timezone,
            $wordlist_file,
            false,
            $conf);
                        
        return $file;        
    }
        
    public static function id_session_anon_create(array $conf, $node){
    
        self::$id_anon = uniqid();
        
        return self::id_init(
            self::hash($node->n.self::$id_anon),
            self::hash($$node->countryName.self::$id_anon),
            self::hash($node->stateOrProvinceName.self::$id_anon),
            self::hash($node->localityName.self::$id_anon),
            self::hash($node->organizationName.self::$id_anon),
            self::hash($node->organizationalUnitName.self::$id_anon),
            self::hash($node->commonName.self::$id_anon),
            self::hash($node->emailAddress.self::$id_anon),
            self::hash($node->telNumber.self::$id_anon),
            self::hash($node->password.self::$id_anon),
            self::hash($node->pgp_passphrase.self::$id_anon),
            self::hash($node->id_lang.self::$id_anon),
            self::hash($node->id_timezone.self::$id_anon),
            self::hash($node->wordlist_file.self::$id_anon),
            false,
            $conf);
    }
    
    public static function id_session_login(){
        
        if(isset($_POST['id_name']) === true) $id_name = urldecode(strip_tags($_POST['id_name']));
        if(isset($_POST['password']) === true) $password = urldecode(strip_tags($_POST['password']));
        if(isset($_POST['cypher_key']) === true) $cypher_key = urldecode(strip_tags($_POST['cypher_key']));
        
        return self::id_get($id_name, $password, $cypher_key);
    }
    
    public static function id_session_load(){
        
        if(isset($_POST['session_id']) === true) $password = urldecode(strip_tags($_POST['session_id']));
        if(isset($_POST['cypher_key']) === true) $cypher_key = urldecode(strip_tags($_POST['cypher_key']));
        
        return self::id_get_from_sesison_id($session_id, $cypher_key);
    }
}