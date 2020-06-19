<?php

trait Pgp_simple {

    use Rsa_simple;

    public static $pgp_dir = 'pgp';
    public static $pgp_env_file = '.gnupg';
    public static $pgp_separator = '____PGP_CUSTOM_SEP____';
    public static $pgp_resource;
    public static $pgp_passphrase_file = 'passphrase.pgp';
    public static $pgp_passphrase;
    public static $pgp_env;

    private static function pgp_init(string $pgp_passphrase):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        putenv('GNUPGHOME='.self::$pgp_env);

        self::$pgp_resource = gnupg_init();
        file_put_contents(self::$pgp_passphrase_file, $pgp_passphrase);

        return true;
    }

    private static function pgp_passphrase_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, self::$pgp_passphrase_file);

        return Env::file_get_contents(self::$pgp_passphrase_file);
    }

    public function pgp_crypt(string $msg, string $rsa_public_key):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $session_key = hash(self::$rsa_digest_alg, self::rsa_public_key_get() . time() . uniqid());

        $pgp_passphrase = self::pgp_passphrase_get();

        gnupg_addencryptkey(self::$pgp_resource, $session_key, $pgp_passphrase);

        $msg_crypted = gnupg_encrypt(self::$pgp_resource, $msg);

        $session_key_crypted = self::rsa_crypt($session_key, $rsa_public_key);

        $cypher = $msg_crypted.self::$pgp_separator.$session_key_crypted;

        return $cypher;
    }

    public function pgp_uncrypt(string $cypher, string $pgp_passphrase):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $cypher_parts = explode(self::$pgp_separator, $cypher);
        $msg_crypted = $cypher_parts[0];
        $session_key_crypted = $cypher_parts[1];
        $session_key = self::rsa_uncrypt($session_key_crypted);
        $plaintext = "";

        gnupg_adddecryptkey(self::$pgp_resource, $session_key, $pgp_passphrase);

        gnupg_decryptverify(self::$pgp_resource, $cypher, $plaintext);

        return $plaintext;
    }

    public static function pgp_init_key_dir(string $n) {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $dir = Env::dir_create(self::$pgp_dir, $n);

        self::$pgp_env = Env::file_set(self::$pgp_dir.'/'.$n.'/'.self::$pgp_env_file);
        self::$pgp_passphrase_file = Env::file_set(self::$pgp_dir.'/'.'/'.self::$pgp_passphrase_file);
    }

}
