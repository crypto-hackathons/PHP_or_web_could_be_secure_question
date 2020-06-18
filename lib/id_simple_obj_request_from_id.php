<?php

class Request_from_id extends Request {

  public $n = false;
  public $countryName = false;
  public $stateOrProvinceName = false;
  public $localityName = false;
  public $organizationName = false;
  public $organizationalUnitName = false;
  public $commonName = false;
  public $emailAddress = false;
  public $telNumber = false;
  public $password = false;
  public $pgp_passphrase = false;
  public $id_lang = false;
  public $id_timezone = false;
  public $wordlist_file = false;
  public $crypt_pgp_state = false;
  public $conf = false;
  public $definition = false;

  public function __construct() {

        l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        parent::__construct();
  }

  public static function build(stdClass $i):array {

    l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $i);

    $_POST['n'] = $i->n;
    $_POST['countryName'] = $i->countryName;
    $_POST['stateOrProvinceName'] = $i->stateOrProvinceName;
    $_POST['localityName'] = $i->localityName;
    $_POST['organizationName'] = $i->organizationName;
    $_POST['organizationalUnitName'] = $i->organizationalUnitName;
    $_POST['commonName'] = $i->commonName;
    $_POST['emailAddress'] = $i->emailAddress;
    $_POST['telNumber'] = $i->telNumber;
    $_POST['password'] = $i->password;
    $_POST['pgp_passphrase'] = $i->pgp_passphrase;
    $_POST['id_lang'] = $i->id_lang;
    $_POST['id_timezone'] = $i->id_timezone;
    $_POST['wordlist_file'] = $i->wordlist_file;
    $_POST['crypt_pgp_state'] = $i->crypt_pgp_state;
    $_POST['conf'] = $i->conf;
    $_POST['definition'] = $i->definition;

    return $_POST;
  }
}
