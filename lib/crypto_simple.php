<?php

/**
 * Trait Crypto_simple
 */
Trait Crypto_simple {

    use Compress_simple, Ppg_simple, Cert_simple, Seed_simple, Sign_simple, Hash_simple, Id_simple;

    /**
     * @var bool
     */
    public static $crypt_pgp_state = true;

    /**
     * @return bool
     */
    public static function crypto_install(){

      $shortopts = "crypt_pgp_state:";
      $shortopts .= "countryName:";
      $shortopts .= "stateOrProvinceName:";
      $shortopts .= "localityName:";
      $shortopts .= "organizationName:";
      $shortopts .= "organizationalUnitName:";
      $shortopts .= "commonName:";
      $shortopts .= "emailAddress:";
      $shortopts .= "password:";
      $shortopts .= "pgp_passphrase:";
      $shortopts .= "seed_grain:";
      $options = getopt($shortopts);

      return self::crypto_init(
          $options['crypt_pgp_state'],
          $options['countryName'],
          $options['stateOrProvinceName'],
          $options['localityName'],
          $options['organizationName'],
          $options['organizationalUnitName'],
          $options['commonName'],
          $options['emailAddress'],
          $options['password'],
          $options['pgp_passphrase'],
          $options['seed_grain']);
  }

    /**
     * @param bool $crypt_pgp_state
     * @param string $countryName
     * @param string $stateOrProvinceName
     * @param string $localityName
     * @param string $organizationName
     * @param string $organizationalUnitName
     * @param string $commonName
     * @param string $emailAddress
     * @param string $password
     * @param string $pgp_passphrase
     * @param string $seed_grain
     * @return bool
     */
    public function crypto_init(
      bool $crypt_pgp_state,
      string $countryName,
      string $stateOrProvinceName,
      string $localityName,
      string $organizationName,
      string $organizationalUnitName,
      string $commonName,
      string $emailAddress,
      string $password,
      string $pgp_passphrase,
        string $wordlist_file)
  {

      self::$crypt_pgp_state = $crypt_pgp_state;

      self::rsa_init();
      self::cert_init($countryName,
          $stateOrProvinceName,
          $localityName, $organizationName,
          $organizationalUnitName,
          $commonName,
          $emailAddress,
          $password);

      self::pgp_init($pgp_passphrase);
      self::seed_init($wordlist_file);
      self::hash_init();

      return true;
  }

    /**
     * @param string $msg
     * @param string $public_key
     * @return bool|string
     */
    public function crypt(string $msg, string $public_key) {

    // process
    $msg = self::compress($msg);

    if(self::$crypt_pgp_state === true) $cypher = self::pgp_crypt($msg, $public_key);
    else                                $cypher = self::rsa_crypt($msg, $public_key);

    return $cypher;
  }

    /**
     * @param string $cypher
     * @return bool|string
     */
    public function uncrypt(string $cypher) {

    if(self::$crypt_pgp_state === true) $msg = self::pgp_uncrypt($cypher);
    else                          $msg = self::rsa_uncrypt($cypher);

    $msg = self::uncompress($msg);

    return $msg;
  }
}
