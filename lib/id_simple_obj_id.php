<?php

class Id {

  public $definition;
  public $conf;
  public $cert;
  public $data;
  public $data_cipher;

  public function __construct(stdClass $definition, stdClass $conf, string $id_lang, string $id_timezone, string $id_commonName, string $id_public_key, array $private_info_list, string $id_sign_public_key, string $otp_private_key_crypted, string $otp_sign_private_key_crypted){

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $id_public_key);

    $this->definition = $definition;
    $this->conf = $conf;
    $this->conf->id_lang = $id_lang;
    $this->conf->id_timezone = $id_timezone;
    $this->conf->id_commonName = $id_commonName;
    $this->cert->time = time();
    $this->cert->public_key = $id_public_key;
    $this->cert->sign_public_key = $id_sign_public_key;

    $this->data = self::rsa_crypt($private_info_list, self::$id_public_key);

    $this->data_cipher = new stdClass();
    $this->data_cipher->private_key_cipher = $otp_private_key_crypted;
    $this->data_cipher->sign_private_key_cipher = $otp_sign_private_key_crypted;
  }
}
