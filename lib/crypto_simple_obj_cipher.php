<?php

class Crypto_simple_obj_cipher {

  Use Compress_simple;

  public $key;
  public $cipher_back;

  public function __construct(string $ciphertext, string $iv, string $tag, string $key) {

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $ciphertext);

    $cipher_back = new stdClass();
    $cipher_back->ciphertext = $ciphertext;
    $cipher_back->iv = $iv;
    $cipher_back->tag = $tag;

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $cipher_back);

    $cipher_back = json_encode($cipher_back);
    if($cipher_back === false) Env::e('Error Json encoding');

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $cipher_back);

    $cipher_back = self::compress($cipher_back);

    $this->key = $key;
    $this->cipher_back = $cipher_back;
  }

  public static function cipher_back_extract(string $compressed):stdClass {

      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

      $data = self::compress(self::$compressed);

      return json_decode($data);
    }
}
