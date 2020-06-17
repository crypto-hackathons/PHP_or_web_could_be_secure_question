<?php

trait Otp_simple {
	
	use Rsa_Simple, Hash_simple;

	public static $otp_id;
	public static $otp_time;
	public static $otp_timeout = false;
	public static $otp_word;
	public static $otp_public_key;
	private static $otp_private_key;
	public static $otp_password;
	public static $otp_pgp_passphrase;
	public static $otp_emailAddress;
	public static $otp_telNumber;

	public static function otp_hash(string $data):string {

		return self::hash($data, self::$otp_id);
	}

	public static function otp_get(string $file, string $otp_id, string $id_emailAddress, string $id_telNumber, string $id_password, string $id_pgp_passphrase, string $id_lang):bool {

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

		self::$otp_word = self::hash_array(json_decode(file_get_contents('../data/wordlist/'.$id_lang.'.json')), self::$otp_id);
		self::$otp_public_key = self::rsa_public_key_get();
		self::$otp_private_key = self::rsa_private_key_get();
		self::$otp_password = self::otp_hash($id_password);
		self::$otp_pgp_passphrase = self::otp_hash($id_pgp_passphrase);
		self::$otp_emailAddress = self::otp_hash($id_emailAddress);
		self::$otp_telNumber = self::otp_hash($id_telNumber);

		return true;
	}

	public function id(){

		self::otp_get($file, $otp_id, $id_emailAddress, $id_telNumber, $id_password, $id_pgp_passphrase, $id_lang);

		self::$id_word = self::$otp_word;
		self::$id_public_key = self::$otp_public_key;
		self::$id_private_key = self::$otp_private_key;
		self::$id_password = self::$otp_password;
		self::$id_pgp_passphrase = self::$otp_pgp_passphrase;
		self::$id_emailAddress = self::$otp_emailAddress;
		self::$id_telNumber = self::$otp_telNumber;

	}
}