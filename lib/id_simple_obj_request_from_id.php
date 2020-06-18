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

  public static function build(string $n, string $countryName, string $stateOrProvinceName, string $localityName, string $organizationName,
    string $organizationalUnitName, string $commonName, string $emailAddress, string $telNumber, string $password, string $pgp_passphrase,
    string $id_lang, string $id_timezone, string $wordlist_file, string $crypt_pgp_state, string $conf, string $definition):Request_from_id {

    l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $n);

    $_POST['n'] = $n;
    $_POST['countryName'] = $countryName;
    $_POST['stateOrProvinceName'] = $stateOrProvinceName;
    $_POST['localityName'] = $localityName;
    $_POST['organizationName'] = $organizationName;
    $_POST['organizationalUnitName'] = $organizationalUnitName;
    $_POST['commonName'] = $commonName;
    $_POST['emailAddress'] = $emailAddress;
    $_POST['telNumber'] = $telNumber;
    $_POST['password'] = $password;
    $_POST['pgp_passphrase'] = $pgp_passphrase;
    $_POST['id_lang'] = $id_lang;
    $_POST['id_timezone'] = $id_timezone;
    $_POST['wordlist_file'] = $wordlist_file;
    $_POST['crypt_pgp_state'] = $crypt_pgp_state;
    $_POST['conf'] = $conf;
    $_POST['definition'] = $definition;

    return $_POST;
  }
}
