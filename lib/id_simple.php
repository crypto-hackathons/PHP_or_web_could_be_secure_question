<?php

trait Id_simple {

    use Hash_simple, Compress_simple, Rsa_simple, Crypto_simple;

    public static $id_dir;
    public static $id_commonName;
    public static $id_name;
    public static $id_anon;
    public static $id_public_key;
    public static $id_lang;
    public static $id_timezone;
    public static $id_sign_public_key;
    public static $id_password_hash;
    public static $id_pgp_passphrase_hash;
    public static $id_emailAddress_hash;
    public static $id_telNumber_hash;
    public static $id_word_hash;
    public static $id_private_key_crypted;
    public static $id_sign_private_key_crypted;

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
        stdClass $conf,
        stdClass $definition):stdClass {

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
        self::sign_init();

        file_put_contents(self::$sign_private_key_file, $private_key_res);

        // clear
        self::$id_commonName = $commonName;
        self::$id_lang = $id_lang;
        self::$id_timezone = $id_timezone;

        $id_hashed = self::hash($password.self::$id_name);
        $file = self::$id_dir->id_dir.'/'.$id_hashed.'.json';

        $crypted_key_keys = self::otp_set($file, $otp_id, $id_emailAddress, $id_telNumber, $id_password, $id_pgp_passphrase, $id_lang);

        // hasheed with otp
        self::$id_word_hash = self::$otp_word_hash;
        self::$id_password_hash = self::$otp_password_hash;
        self::$id_pgp_passphrase_hash = self::$otp_pgp_passphrase_hash;
        self::$id_emailAddress_hash = self::$otp_emailAddress_hash;
        self::$id_telNumber_hash = self::$otp_telNumber_hash;

        // clear
        self::$id_public_key = self::$otp_public_key;
        self::$id_sign_public_key = self::$otp_sign_public_key;

        // Crypted
        self::$id_private_key_crypted = self::$otp_private_key_crypted;
        self::$id_sign_private_key_crypted = self::$otp_sign_private_key_crypted;

        // clear
        $id = new stdClass();
        $id->definition = $definition;
        $id->conf = $conf;
        $id->conf->id_lang = self::$id_lang;
        $id->conf->id_timezone = self::$id_timezone;
        $id->conf->id_commonName = self::$id_commonName;
        $id->cert = new stdClass();
        $id->cert->time = time();
        $id->cert->public_key = self::$id_public_key;
        $id->cert->sign_public_key = self::$id_sign_public_key;

        // RSA crypted
        $id->data = self::rsa_crypt(json_encode(get_class_vars(get_class($this))), self::$id_public_key);

        $data = self::audit_object($id, $id->sign->sign_public_key, self::$otp_id);

        file_put_contents($file, $data);

        return $crypted_key_keys;
    }

    public static function id_dir_create(string $dir, string $n, string $dn): bool{

        $dir = $dn.'/../data/'.$dir.'/'.$n;

        if(is_dir($dir) === false) return mkdir($dir, 777, true);

        return true;
    }

    public static function id_dir_create_all(string $n, string $dn, array $dir_list = array('key', 'cert', 'pgp', 'seed', 'id')):stdClass {

        $dir = new stdClass();

        foreach($dir_list as $k => $v) {

            $dir->$k = self::id_dir_create($v, $n, $dn);
        }
        return $dir;
    }

    public static function id_get_from_otp_id(string $password, string $id_name, string $otp_id, string $cypher_key, string $definition): string {

        $id_hashed = self::hash($password.$id_name);

        self::otp_verify($file, $otp_id, $otp_name);

        $file = self::$id_dir->id_dir.'/'.$id_hashed.'.json';

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

    public static function id_get(string $id_name, string $password, string $cypher_key, stdClass $definition):string {

        $session_id = self::hash($password.$id_name);

        file_put_contents('../data/session/'.$session_id.'_'.uniqid().'.txt', $session_id.';'.self::hash($id_name));

        return self::id_get_from_sesison_id($session_id, $cypher_key, $definition);
    }

    public static function id_session_init(array $conf, $node, $definition):string {

        if(isset($_GET['sessionCreate']) === false) {

            return self::id_session_create($conf);
        }
        elseif(isset($_GET['sessionlogin']) === false) {

            return self::id_session_login();
        }
        elseif(isset($_GET['session']) === false) {

           return $session_id = self::id_session_anon_create($conf, $node, $definition);
        }
        elseif(isset($_GET['session']) === true) {

            return $session_id = self::id_session_load();
        }
    }

    public static function id_session_create(stdClass $conf, stdClass $definition):stdClass {

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
        if(isset($_POST['definition']) === true) $definition = urldecode(strip_tags($_POST['definition']));

        $std = self::id_init(
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
            $conf,
            $definition);

        return $std;
    }

    public static function id_session_anon_create(array $conf, stdClass $node, stdClass $definition):stdClass {

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
            $conf,
            $definition);
    }

    public static function id_session_login():stdClass {

        if(isset($_POST['id_name']) === true) $id_name = urldecode(strip_tags($_POST['id_name']));
        if(isset($_POST['password']) === true) $password = urldecode(strip_tags($_POST['password']));
        if(isset($_POST['cypher_key']) === true) $cypher_key = urldecode(strip_tags($_POST['cypher_key']));
        if(isset($_POST['definition']) === true) $definition = urldecode(strip_tags($_POST['definition']));

        return self::id_get($id_name, $password, $cypher_key, $definition);
    }

    public static function id_session_load():stdClass {

        if(isset($_POST['session_id']) === true) $password = urldecode(strip_tags($_POST['session_id']));
        if(isset($_POST['cypher_key']) === true) $cypher_key = urldecode(strip_tags($_POST['cypher_key']));
        if(isset($_POST['definition']) === true) $definition = urldecode(strip_tags($_POST['definition']));

        return self::id_get_from_sesison_id($session_id, $cypher_key, $definition);
    }
}
