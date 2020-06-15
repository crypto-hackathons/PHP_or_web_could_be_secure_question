<?php 

trait Id_simple {
    
    use Hash_simple, Compress_simple, Rsa_simple;
    
    public static function id_init() {
        
        self::hash_init();
    }
    
    public function id_gen_cert_data(string $countryName, string $stateOrProvinceName, string $localityName, string $organizationName): stdClass {
        
        $cert_data = new stdClass();
        $cert_data->countryName = hash('sha256', self::$hash_prefix.$countryName);
        $cert_data->stateOrProvinceName = hash('sha256', self::$hash_prefix.$stateOrProvinceName);
        $cert_data->localityName = hash('sha256', self::$hash_prefix.$localityName);
        $cert_data->organizationName = hash('sha256', self::$hash_prefix.$organizationName);
        
        return $cert_data;
    }
    
    public function id_gen(array $conf, string $commonName, string $n, string $emailAddress, string $telNumber, string $password, string $pgp_passphrase, $cert_data = false, bool $crypt_pgp_state = false, string $wordlist_file = self::$SEED_FRENCH_WORDLIST_FILE):stdClass {
                
        $dn = './';
        
        if(is_dir($dn.'/../data/key/'.$n) === false) mkdir($dn.'/../data/key/'.$n, 777, true);
        if(is_dir($dn.'/../data/cert/'.$n) === false) mkdir($dn.'/../data/cert/'.$n, 777, true);
        if(is_dir($dn.'/../data/pgp/'.$n) === false) mkdir($dn.'/../data/pgp/'.$n, 777, true);
        if(is_dir($dn.'/../data/seed/'.$n) === false) mkdir($dn.'/../data/seed/'.$n, 777, true);
        
        self::$rsa_public_key_file = $dn.'/../data/key/'.$n.'/private.pem';
        self::$rsa_private_key_file = $dn.'/../data/key/'.$n.'/public.pem';
        self::$cert_csr_file = $dn.'/../data/cert/'.$n.'/src.pem';
        self::$cert_x509_file = $dn.'/../data/cert/'.$n.'/x509.pem';
        self::$cert_pkey_file = $dn.'/../data/cert/'.$n.'/private_pwd.pem';
        self::$pgp_env = $dn.'/../data/pgp/'.$n.'/.gnupg';
        self::$pgp_passphrase_file = $dn.'/../data/pgp/'.$n.'/passphrase.pgp';
        self::$seed_private_key_master_dir = $dn.'/../data/seed/'.$n.'/';
        self::$seed_grain_file = $dn.'/../data/seed/'.$n.'/grain.txt';
        self::$SEED_CHINESE_SIMPLIFIED_WORDLIST_FILE = $dn.'/../data/wordlists/chinese_simplified.json';
        self::$SEED_CHINESE_TRADITIONAL_WORDLIST_FILE = $dn.'/../data/wordlists/chinese_traditional.json';
        self::$SEED_ENGLISH_WORDLIST_FILE = $dn.'/../data/wordlists/english.json';
        self::$SEED_FRENCH_WORDLIST_FILE = $dn.'/../data/wordlists/french.json';
        self::$SEED_ITALIAN_WORDLIST_FILE = $dn.'/../data/wordlists/italian.json';
        self::$SEED_JAPANESE_WORDLIST_FILE = $dn.'/../data/wordlists/japanese.json';
        self::$SEED_KOREAN_WORDLIST_FILE = $dn.'/../data/wordlists/korean.json';
        self::$SEED_SPANISH_WORDLIST_FILE = $dn.'/../data//wordlists/spanish.json';
        self::$SEED_DEFAULT_WORDLIST_FILE = $dn.'/../data/wordlists/english.json';
        
        $crypt_pgp_state = false;
        $organizationalUnitName = self::hash($n);
        $password = self::hash($password);
        $pgp_passphrase = self::hash($pgp_passphrase);
        $wordlist_file = Distribute::$SEED_FRENCH_WORDLIST_FILE;
        $wordlist = json_decode(file_get_contents($wordlist_file));
        $emailAddress = self::hash($emailAddress);
        $telNumber = self::hash($telNumber);
        $commonName = self::hash($commonName);
        
        if($cert_data !== false) {
            
            $this->crypto_init($crypt_pgp_state, $cert_data->countryName, $cert_data->stateOrProvinceName, $cert_data->localityName, $cert_data->organizationName, $organizationalUnitName, $commonName, $emailAddress, $password, $pgp_passphrase, $wordlist_file);
        }
        else {
            
            $this->crypto_init($crypt_pgp_state, '', '', '', '', '', $commonName, $emailAddress, $password, $pgp_passphrase, $wordlist_file);
        }
        $id = new stdClass();
        $id->data = new stdClass();
        $id->data->pub_key = $d::rsa_public_key_get();
        $id->data->wordlist = self::hash_array($wordlist);
        $id->data->emailAddress = self::hash($emailAddress);
        $id->data->telNumber = self::hash($telNumber);
        $id->data->commonName = self::hash($commonName);
        $id->data->password = self::hash($password);
        $id->data->pgp_passphrase = self::hash($pgp_passphrase);
        foreach ($conf as $k => $v) $id->conf[$k] = self::hash($v);
        $id->cert->time = time();
        $id->cert->node_sign = '';
        $id->hash = self::hash(json_encode($id->data).json_encode($id->cert));
        
        return $id;
    }
    public function id_create(array $conf, string $commonName, string $n, string $emailAddress, string $telNumber, string $password, string $pgp_passphrase, $cert_data = false, bool $crypt_pgp_state = false, string $wordlist_file = self::$SEED_FRENCH_WORDLIST_FILE):stdClass {
        
        $id = $this->id_gen($conf, $commonName, $n, $emailAddress, $telNumber, $password, $pgp_passphrase, $cert_data, $crypt_pgp_state, $wordlist_file);
        
        $data = json_encode($id, JSON_PRETTY_PRINT);
        $data = self::rsa_crypt($data, $id->pub_key);
        $data = self::compress($data);
        
        return file_put_contents('../data/id/'.self::hash($id->hash).'.json' , $data);
    }
    public function id_get(string $hash){
        
        $file = '../data/id/'.self::hash($hash).'.json';
        
        if(is_file($file) === false) error('Bad session creation.');
        
        $data = file_get_contents('../data/id/'.self::hash($hash).'.json');
        $data = self::uncompress($data);
        $data = self::rsa_uncrypt($data);
        
        return json_decode($data);
    }
}