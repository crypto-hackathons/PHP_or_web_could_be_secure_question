<?php

/**
 * Trait Crypto_simple
 */
Trait Crypto_simple {

    use Compress_simple;

    public static $crypto_cipher = 'aes-128-gcm';
    public static $crypto_key_size = 2018;

    public static function crypto_crypt(string $plaintext){

        $key = openssl_random_pseudo_bytes(self::$crypto_key_size, true);
        $ivlen = openssl_cipher_iv_length(self::$crypto_cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($plaintext, self::$crypto_cipher, $key, $options=0, $iv, $tag);

        $cipher_back = new stdClass();
        $cipher_back->ciphertext = $ciphertext;
        $cipher_back->iv = $iv;
        $cipher_back->tag = $tag;

        $cipher_back = json_encode(self::$cipher_back);
        $cipher_back = self::compress(self::$cipher_back);

        $result = new stdClass();
        $result->ciphertext = $ciphertext;
        $result->cipher_back = $cipher_back;

        return self::$result;
    }

    public static function crypto_uncrypt($cipher_back, string $cipher_key){

        $cipher_back = self::uncompress($cipher_back);
        $cipher_back = json_decode($cipher_back);

        return openssl_decrypt($cipher_back->ciphertext, self::$crypto_cipher, $cipher_key, $options=0, $cipher_back->iv, $cipher_back->tag);
    }
}
