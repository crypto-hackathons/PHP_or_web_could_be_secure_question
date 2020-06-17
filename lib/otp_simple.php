<?php

trait Otp_simple {

	use Rsa_Simple, Hash_simple, Sign_simple;

	public static $otp_id;
	public static $otp_time;
	public static $otp_timeout = false;
	public static $otp_public_key;
	public static $otp_sign_public_key;
	public static $otp_word_hash;
	public static $otp_password_hash;
	public static $otp_pgp_passphrase_hash;
	public static $otp_emailAddress_hash;
	public static $otp_telNumber_hash;
	public static $otp_private_key_crypted;
	public static $otp_sign_private_key_crypted;

	public static function otp_hash(string $data):string {

		return self::hash($data, self::$otp_id);
	}

	public static function otp_set(string $file, string $otp_id, string $id_emailAddress, string $id_telNumber, string $id_password, string $id_pgp_passphrase, string $id_lang):stdClass {

		$file_tmp = explode('.', basename($file));
		$file_tmp_end = end($file_tmp);
		$file = str_replace($file_tmp_end, 'otp_'.$file_tmp_end);
		$file_otp = '../data/otp/'.$file;

		$i = explode(';', file_get_contents($file_otp));
		self::$otp_time = trim($i[0]);
		self::$otp_timeout = trim($i[1]);

		if(self::$otp_timeout === '0') self::$otp_timeout = false;

		self::$otp_id = trim($i[2]);
		self::$otp_name = trim($i[3]);

		if($otp_id !== self::$otp_id)  error('Otp error.');

		if(self::$otp_timeout !== false && (time() - self::$otp_time)) > self::$otp_timeout) error('Otp timeout');

		self::$otp_id = uniqid();
		self::$otp_time = time();

		file_put_contents($file_otp, self::$otp_time.';'.self::$otp_timeout.':'.self::$otp_id.';'.self::$otp_name);

		// hashed with otp
		self::$otp_word_hash = self::hash_array(json_decode(file_get_contents('../data/wordlist/'.$id_lang.'.json')), self::$otp_id);
		self::$otp_password_hash = self::otp_hash($id_password);
		self::$otp_pgp_passphrase_hash = self::otp_hash($id_pgp_passphrase);
		self::$otp_emailAddress_hash = self::otp_hash($id_emailAddress);
		self::$otp_telNumber_hash = self::otp_hash($id_telNumber);

		// clear
		self::$otp_public_key = self::rsa_public_key_get();
		self::$otp_sign_public_key = self::sign_public_key_get();

		// crypted
		$otp_private_key = self::rsa_private_key_get();
		$crypto_crypt = self::crypto_crypt(self::$otp_private_key);
		self::$otp_private_key_crypted = $crypto_crypt->ciphertext;
		$otp_sign_private_key = self::sign_private_key_get();
		$crypto_crypt = self::crypto_crypt(self::$otp_sign_private_key);
		self::$otp_sign_private_key = $crypto_crypt->ciphertext;

		$result = new stdClass();
		$result->otp_private_key_crypted_key = $crypto_crypt->cipher_back;
		$result->$otp_sign_private_key_key = $crypto_crypt->cipher_back_key;

		return $result;
	}
}
