<?php

trait Rsa_simple
{
    public static $rsa_dir = 'rsa';
    public static $rsa_public_key_file = 'private.pem';
    public static $rsa_private_key_file = 'public.pem';
    private static $rsa_digest_alg = 'sha512';
    private static $rsa_private_key_bits = 4096;
    private static $rsa_private_key_type = OPENSSL_KEYTYPE_RSA;
    private static $rsa_key_days = 365;

    public $rsa_public_key;

    private static function rsa_init($name = 'mine'):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $config = array(
            'digest_alg' => self::$rsa_digest_alg,
            'private_key_bits' => self::$rsa_private_key_bits,
            'private_key_type' => self::$rsa_private_key_type);
        $res = openssl_pkey_new($config);
        $privKey = '';

        openssl_pkey_export($res, $privKey);

        Env::file_put_contents(self::$rsa_private_key_file, $privKey);

        $pubKey = openssl_pkey_get_details($res);

        Env::file_put_contents(self::$rsa_public_key_file, $pubKey["key"]);

        return true;
    }

    public static function rsa_public_key_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, self::$rsa_public_key_file);

       return Env::file_get_contents(self::$rsa_public_key_file);
    }

    public static function rsa_crypt(string $msg, $rsa_public_key = false):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        if ($rsa_public_key === false) $rsa_public_key = self::rsa_public_key_get();

        $pk = openssl_get_publickey($rsa_public_key);

        if ($pk === false) return false;

        $finaltext = '';

        openssl_public_encrypt($msg, $finaltext, $pk);

        if (empty($finaltext) === false) openssl_free_key($pk);
        else return false;

        return $finaltext;
    }


    private static function rsa_private_key_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, self::$rsa_private_key_file);

        return Env::file_get_contents(self::$rsa_private_key_file);
    }

    public static function rsa_uncrypt(string $msg_crypted):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $rsa_private_key = self::rsa_private_key_get();
        $msg_decrypted = '';

        $pk2 = openssl_get_privatekey($rsa_private_key);
        $res = openssl_private_decrypt($msg_crypted,$msg_decrypted, $pk2);

        if ($res === false) return false;

        return $msg_decrypted;
    }

    public static function rsa_init_key_dir(string $n) {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $dir = Env::dir_create(self::$rsa_dir, $n);

        self::$rsa_public_key_file = self::$rsa_dir.'/'.$n.'/'.self::$rsa_public_key_file;
        self::$rsa_private_key_file = self::$rsa_dir.'/'.$n.'/'.self::$rsa_private_key_file;
    }
}
