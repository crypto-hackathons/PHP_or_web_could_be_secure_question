<?php

class Key_crypted_parts{

  public $private_key_crypted_key;
  public $sign_private_key_crypted_key;

  public function __construct(string $private_cipher_back_key, string $sign_private_ccipher_back_key) {

    $this->private_key_crypted_key = $private_cipher_back_key;
    $this->sign_private_key_crypted_key = $sign_private_ccipher_back_key;
  }
}
