<?php

trait Cert_simple {

    use Rsa_simple;

    public static $cert_dir = 'cert';
    public static $cert_csr_file = 'src.pem';
    public static $cert_x509_file = 'x509.pem';
    public static $cert_pkey_file = 'private_pwd.pem';
    private static $cert_password;
    private static $cert_user_data;
    private static $cert_client;
    private static $cert_client_signed;

    public static function cert_client_set(string $cert_client):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        self::$cert_client = $cert_client;

        return true;
    }

    private static function cert_init(string $countryName,
                                     string $stateOrProvinceName,
                                     string $localityName,
                                     string $organizationName,
                                     string $organizationalUnitName,
                                     string $commonName,
                                     string $emailAddress, string $password):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        self::$cert_user_data = array(
        'countryName' => $countryName,
        'stateOrProvinceName' => $stateOrProvinceName,
        'localityName' => $localityName,
        'organizationName' => $organizationName,
        'organizationalUnitName' => $organizationalUnitName,
        'commonName' => $commonName,
        'emailAddress' => $emailAddress);

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, self::$cert_user_data);

        self::$cert_password = $password;

        $privkey = self::rsa_private_key_get();

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $privkey);

        $csr = openssl_csr_new(self::$cert_user_data, $privkey);
        $sscert = openssl_csr_sign($csr, null, $privkey, self::$rsa_key_days);

        openssl_csr_export($csr, $csrout);

        file_put_contents(self::$cert_csr_file, $csrout);

        openssl_x509_export($sscert, $certout);

        file_put_contents(self::$cert_x509_file, $certout);

        openssl_pkey_export($privkey, $pkeyout, self::$cert_password);

        file_put_contents(self::$cert_pkey_file, $pkeyout);

        return true;
    }

    public static function cert_client_sign(): bool{

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $usercert = openssl_csr_sign(self::$cert_client, self::cert_x509_get(), self::rsa_private_key_get(), self::$rsa_key_days);

        openssl_x509_export($usercert, $csrout);

        self::$cert_client_signed = $csrout;

        return true;
    }

    public static function cert_init_key_dir(string $n) {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $n);

        $dir = Env::dir_create(self::$cert_dir, $n);

        self::$cert_csr_file = Env::file_set(self::$cert_dir.'/'.$n.'/'.self::$cert_csr_file);
        self::$cert_x509_file = Env::file_set(self::$cert_dir.'/'.'/'.self::$cert_x509_file);
        self::$cert_pkey_file = Env::file_set(self::$cert_dir.'/'.'/'.self::$cert_pkey_file);
    }

}
