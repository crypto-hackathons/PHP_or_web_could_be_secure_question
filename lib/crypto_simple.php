<?php

/**
 * Trait Crypto_simple
 */
Trait Crypto_simple {

    use Compress_simple;

    public static $crypto_cipher = 'aes-128-gcm';
    public static $crypto_key_size = 2018;

    public static function crypto_crypt(string $plaintext):string {

        $key = openssl_random_pseudo_bytes(self::$crypto_key_size, true);
        $ivlen = openssl_cipher_iv_length(self::$crypto_cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($plaintext, self::$crypto_cipher, $key, $options=0, $iv, $tag);

        return new Crypto_simple_obj_cipher($ciphertext, $iv, $tag, $key);
    }

    public static function crypto_uncrypt(string $cipher_back, string $cipher_key):string {

        $cipher_back = Crypto_simple_obj_cipher::cipher_back_extract($cipher_back);

        return openssl_decrypt($cipher_back->ciphertext, self::$crypto_cipher, $cipher_key, $options=0, $cipher_back->iv, $cipher_back->tag);
    }
}
