<?php

trait Id_simple {

    use Crypto_simple, Hash_simple, Compress_simple, Rsa_simple, Crypto_simple, Cert_simple, Pgp_simple;

    public static $id_dir_global;
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
    public static $id_dir_list = array('key', 'cert', 'pgp', 'seed', 'id');
    public static $id_node_file = 'node/id.json';

    public static function id_session_otp_create_from_info():stdClass {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $info = new Request_from_id();
        self::hash_init();
        self::$id_name = $info->n;
        $name_hashed =  self::hash($info->n);
        self::$id_dir = self::id_dir_create_all($name_hashed);
        self::rsa_init_key_dir(self::$id_dir->key);
        self::cert_init_key_dir(self::$id_dir->cert);
        self::pgp_init_key_dir(self::$id_dir->pgp);
        self::seed_init_key_dir(self::$id_dir->seed);
        self::rsa_init();
        self::cert_init(self::hash($info->countryName), self::hash($info->stateOrProvinceName),
          self::hash($info->localityName), self::hash($info->organizationName), self::hash($info->organizationalUnitName),
          self::hash($info->commonName), self::hash($info->emailAddress), self::hash($info->password));
        self::pgp_init(self::hash($info->pgp_passphrase));
        self::seed_init($info->wordlist_file);
        self::sign_init();

        // clear
        self::$id_commonName = $info->commonName;
        self::$id_lang = $info->id_lang;
        self::$id_timezone = $info->id_timezone;

        $id_hashed = self::hash($info->password.self::$id_name);
        $file = self::$id_dir->id_dir.'/'.$info->id_hashed.'.json';

        $crypted_key_keys = self::otp_set($file, $info->otp_id, $info->id_emailAddress, $info->id_telNumber,
          $info->id_password, $info->id_pgp_passphrase, $info->id_lang);

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
        $id = new Id($info->definition, $info->conf, self::$id_lang, self::$id_timezone, self::$id_commonName,
          self::$id_public_key, json_encode(get_class_vars(get_called_class())), self::$id_sign_public_key);

        $data = self::audit_object($id, $id->sign->sign_public_key, self::$otp_id);

        file_put_contents($file, $data);

        return $crypted_key_keys;
    }

    public static function id_dir_create_all(string $n):stdClass {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $n);

        $dir = new stdClass();

        foreach(self::$id_dir_list as $v) {

            $dir->$v = Env::dir_create($v, $n);
        }
        return $dir;
    }

    public static function id_session_get_from_otp():stdClass {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $info = new Request_from_otp();

        $id_hashed = self::hash($info->password.$info->id_name);
        $file = self::$id_dir_global.'/'.$info->id_hashed.'.json';

        if(is_file($file) === false) Env::e('Id not found');

        self::otp_verify($file, $info->otp_id, $info->otp_name);

        $data = file_get_contents($file);

        $id->cert->public_key = self::$id_public_key;
        $id->cert->sign_public_key = self::$id_sign_public_key;

        $id = audit_verify($data, self::$otp_id);

        $id->data_cipher->private_key_cipher = self::$otp_private_key_crypted;
        $id->data_cipher->sign_private_key_cipher = self::$otp_sign_private_key_crypted;

        self::$otp_private_key = self::crypto_uncrypt($id->data_cipher->private_key_cipher, $private_key_crypted_key);
        self::$otp_sign_private_key = self::crypto_uncrypt($id->data_cipher->private_key_cipher, $sign_private_key_crypted_key);

        $id->data = self::rsa_uncrypt($id);

        return $id;
    }

    public static function id_session_init():string {

      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

      $otpCreate = Request::info_from_get('otpCreate');

      if(empty($otpCreate) === true) $otpCreate = false;

      $otp = Request::info_from_get('otp');

      if(empty($otp) === true) $otp = false;

      if($otpCreate === true) {

          return self::id_session_otp_create_from_info();
      }
      elseif($otp === true) {

          return self::id_session_get_from_otp();
      }
      elseif($otp === false) {

         return self::id_session_otp_create();
      }
    }

    public static function id_session_otp_create():stdClass {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $i = Env::file_get_contents_json(self::$id_node_file);
        $anon = '_'.uniqid();
        $i->n .= $anon;
        $i->countryName .= $anon;
        $i->stateOrProvinceName .= $anon;
        $i->localityName .= $anon;
        $i->organizationName .= $anon;
        $i->organizationalUnitName .= $anon;
        $i->commonName .= $anon;
        $i->emailAddress .= $anon;
        $i->telNumber .= $anon;
        $i->password .= $anon;
        $i->pgp_passphrase .= $anon;
        $i->conf = json_encode($i->conf);
        $i->definition = json_encode($i->definition);

        Request_from_id::build($i);

        return self::id_session_otp_create_from_info();
    }
}
