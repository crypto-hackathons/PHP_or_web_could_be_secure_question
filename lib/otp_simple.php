<?php

trait Otp_simple {

	use Rsa_Simple, Hash_simple, Sign_simple;

	public static $otp_dir = 'otp';
	public static $otp_file;
	public static $otp_id;
	public static $otp_name;
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

		Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

		return self::hash($data, self::$otp_id);
	}

	public static function otp_set(string $file, string $otp_id, string $otp_name, string $id_emailAddress, string $id_telNumber, string $id_password,
	string $id_pgp_passphrase, string $id_lang):Key_crypted_parts {

		Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

		$file_tmp = explode('.', basename($file));
		$file_tmp_end = end($file_tmp);
		$file = str_replace($file_tmp_end, 'otp_'.$file_tmp_end, $file);
		$file_otp = self::$otp_dir.basename($file);

		if(is_file($file_otp) === true) {

			$i = explode(';', file_get_contents($file_otp));

			self::$otp_file = trim($i[0]);
			self::$otp_time = trim($i[1]);
			self::$otp_timeout = trim($i[2]);

			if(self::$otp_timeout === '0') self::$otp_timeout = false;

			self::$otp_id = trim($i[3]);
			self::$otp_name = trim($i[4]);
		}
		else {

			self::$otp_id = $otp_id;
			self::$otp_file = $file;
			self::$otp_name = $otp_name;
		}

		if($otp_id !== self::$otp_id)  Env::e('Otp error.');
		if(self::$otp_timeout !== false && (time() - self::$otp_time) > self::$otp_timeout) Env::e('Otp timeout');
		if(self::$otp_name !== $otp_name) Env::e('Otp name');

		self::$otp_id = uniqid();
		self::$otp_time = time();

		file_put_contents($file_otp, self::$otp_file.';'.self::$otp_time.';'.self::$otp_timeout.':'.self::$otp_id.';'.self::$otp_name);

		// hashed with otp
		self::$otp_word_hash = self::hash_array(Env::file_get_contents_json(self::$word_dir.'/'.$id_lang.'.json'), self::$otp_id);
		self::$otp_password_hash = self::otp_hash($id_password);
		self::$otp_pgp_passphrase_hash = self::otp_hash($id_pgp_passphrase);
		self::$otp_emailAddress_hash = self::otp_hash($id_emailAddress);
		self::$otp_telNumber_hash = self::otp_hash($id_telNumber);

		// clear
		self::$otp_public_key = self::rsa_public_key_get();
		self::$otp_sign_public_key = self::sign_public_key_get();

		// crypted
		$otp_private_key = self::rsa_private_key_get();
		$crypto_crypt = self::crypto_crypt($otp_private_key);
		self::$otp_private_key_crypted = $crypto_crypt->cipher_back;
		$private_cipher_back_key = $crypto_crypt->cipher_back->key;

		$otp_sign_private_key = self::sign_private_key_get();
		$crypto_crypt = self::crypto_crypt(self::$otp_sign_private_key);
		self::$otp_sign_private_key_crypted = $crypto_crypt->cipher_back;
		$sign_private_cipher_back_key = $crypto_crypt->key;

		return new Key_crypted_parts($private_cipher_back_key, $sign_private_cipher_back_key);
	}

  function otp_verify(string $file, string $otp_id, string $otp_name):bool {

		Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

		$file_otp = self::$otp_dir.basename($file);

		if(is_file($file_otp) === false) Env::e('Otp not found');

		$i = explode(';', file_get_contents($file_otp));
		self::$otp_file = trim($i[0]);
		self::$otp_time = trim($i[1]);
		self::$otp_timeout = trim($i[2]);
		self::$otp_id = trim($i[3]);
		self::$otp_name = trim($i[4]);

		if($otp_id !== self::$otp_id)  Env::e('Otp error.');
		if(self::$otp_timeout !== false && (time() - self::$otp_time) > self::$otp_timeout) Env::e('Otp timeout');
		if(self::$otp_name !== $otp_name) Env::e('Otp name');

		return true;
	}
	public static function otp_init_dir(string $n) {

			Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

			Env::dir_create(self::$otp_dir, $n);
	}

}
