<?php

class Crypto_simple_obj_cipher {

  public $key;
  public $cipher_back;

  public function __construct(string $ciphertext, string $iv, string $tag, string $key) {

    $cipher_back = new stdClass();
    $cipher_back->ciphertext = $ciphertext;
    $cipher_back->iv = $iv;
    $cipher_back->tag = $tag;

    $cipher_back = json_encode(self::$cipher_back);
    $cipher_back = self::compress(self::$cipher_back);

    $this->key = $key;
    $this->cipher_back = $cipher_back;
  }

  public static function cipher_back_extract(string $compressed):stdClass {

      $data = self::compress(self::$compressed);
      return json_decode(self::$data);
    }
}
