<?php

Trait Sign_simple
{
    public static $sign_dir = 'sign';
    public static $sign_private_key_bits = 2048;
    public static $sign_private_key_type = OPENSSL_KEYTYPE_RSA;
    public static $sign_algo = OPENSSL_ALGO_SHA1;
    public static $sign_public_key_file = 'private.pem';
    public static $sign_private_key_file = 'public.pem';
    public static $sign_public_key;

    public static function sign_init():bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $res = openssl_pkey_new(array(
            "private_key_bits" => self::$sign_private_key_bits,
            "private_key_type" => self::$sign_private_key_type,
        ));

        openssl_pkey_export($res, $private_key);

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, self::$sign_private_key_file);

        Env::file_put_contents(self::$sign_private_key_file, $private_key);

        $details = openssl_pkey_get_details($res);

        Env::file_put_contents(self::$sign_public_key_file, $details['key']);

        return true;
    }

    public static function sign(string $data):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $private_key_res = self::sign_private_key_get();
        $signature = openssl_sign($data, $signature, $private_key_res, self::$sign_algo);

        return $signature;
    }

    public static function sign_private_key_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        return Env::file_get_contents(self::$sign_private_key_file);
    }

    public static function sign_verify(string $data, string $signature, $public_key_res = false):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        if ($public_key_res === false) {

            $public_key_res = self::sign_public_key_get();
        }
        $res = openssl_verify($data, $signature, $public_key_res, self::$sign_algo);

        if ($res == 1) return true;

        return false;
    }

    public static function sign_public_key_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        return Env::file_get_contents(self::$sign_public_key_file);
    }

    public static function sign_init_key_dir(string $n) {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $n);

        $dir = Env::dir_create(self::$sign_dir, $n);

        self::$sign_public_key_file = self::$sign_dir.'/'.$n.'/'.self::$sign_public_key_file;
        self::$sign_private_key_file = self::$sign_dir.'/'.$n.'/'.self::$sign_private_key_file;
    }
}
