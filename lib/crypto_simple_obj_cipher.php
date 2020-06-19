<?php

class Crypto_simple_obj_cipher {

  Use Compress_simple;

  public $key;
  public $cipher_back;

  public function __construct(string $ciphertext, string $iv, string $tag, string $key) {

      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $ciphertext);
      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $iv);
      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $tag);
      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $key);

    $cipher_back = new stdClass();
    $cipher_back->ciphertext = $ciphertext;
    $cipher_back->iv = base64_encode($iv);
    $cipher_back->tag = base64_encode($tag);

    $cipher_back = json_encode($cipher_back);
    if($cipher_back === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $cipher_back);

    $cipher_back = self::compress($cipher_back);

    $this->key = $key;
    $this->cipher_back = $cipher_back;
  }

  public static function cipher_back_extract(string $compressed):stdClass {

      Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

      $data = self::compress(self::$compressed);
      $data = json_decode($data);
      $data->iv = base64_decode($data->iv);
      $data->tag = base64_decode($data->tag);

      if($data === false) Env::e('Error Json decoding', __CLASS__.'::'.__METHOD__.'::'.__LINE__);

      return $data;
    }
}
