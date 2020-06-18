<?php

class  Request_from_otp extends Request {

  public $password = false;
  public $id_name = false;
  public $otp_id = false;
  public $private_key_crypted_key = false;
  public $sign_private_key_crypted_key = false;
  public $definition = false;

    public function __construct() {

      l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

      parent::__construct();
    }
}
