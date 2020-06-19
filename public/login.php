<?php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('../lib/rsa_simple.php');
require_once('../lib/cert_simple.php');
require_once('../lib/compress_simple.php');
require_once('../lib/pgp_simple.php');
require_once('../lib/sign_simple.php');
require_once('../lib/seed_simple.php');
require_once('../lib/hash_simple.php');
require_once('../lib/otp_simple.php');
require_once('../lib/audit_simple.php');
require_once('../lib/crypto_simple.php');
require_once('../lib/id_simple.php');
require_once('../lib/crypto_simple.php');
require_once('../lib/merkle_tree_simple.php');

require_once('../lib/id_simple_obj_id.php');
require_once('../lib/id_simple_obj_request.php');
require_once('../lib/id_simple_obj_request_from_id.php');
require_once('../lib/id_simple_obj_request_from_otp.php');
require_once('../lib/crypto_simple_obj_cipher.php');
require_once('../lib/hash_simple_obj_array_hashed.php');
require_once('../lib/opt_simple_obj_key_crypted_parts.php');

Class Env {

  public static $dir_root = '.';
  public static $data_dir_global = '../data';
  public static $data_dir_global_right = 0777; // @TODO /!\ to change

  public static function dir_create(string $dir, string $n):string {

      self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $dir);

      $dir = str_replace('../', '', $dir);
      $dir = str_replace('./', '', $dir);
      $n = str_replace('../', '', $n);
      $n = str_replace('./', '', $n);

      self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $n);

      $dir = self::$dir_root.'/'.Env::$data_dir_global.'/'.$dir.'/'.$n;

      self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $dir);

      if(is_dir($dir) === false){

        self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $dir);

        mkdir($dir, self::$data_dir_global_right, true);
      }
      return $dir;
  }

  public static function file_set(string $file):string {

    self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

    $file = str_replace('../', '', $file);
    $file = str_replace('./', '', $file);

    return self::$dir_root.'/'.self::$data_dir_global.'/'.$file;
  }

  public static function file_put_contents(string $file, string $data):string {

    self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

    $file = str_replace('../', '', $file);
    $file = str_replace('./', '', $file);

    return file_put_contents(self::$dir_root.'/'.self::$data_dir_global.'/'.$file, $data);
  }

  public static function file_get_contents(string $file):string {

    self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

    $file = str_replace('../', '', $file);
    $file = str_replace('./', '', $file);

    return file_get_contents(self::$dir_root.'/'.self::$data_dir_global.'/'.$file);
  }

  public static function file_get_contents_json(string $file):stdClass {

    self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

    $file = str_replace('../', '', $file);
    $file = str_replace('./', '', $file);

    return json_decode(self::file_get_contents($file));
  }

  static function e(string $error, string $error_redirect = 'location: login.php?error='){

      self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $error);

      header($error_redirect.$error);
      exit;
  }

  static function l(string $msg, $info = '', string $eol = '<br>') {

      echo $msg.' --- '.json_encode($info).$eol;
  }
}

class Distribute {

    use Id_simple;
}
$conf = json_decode(file_get_contents('../data/conf/global.json'));
$d = new Distribute();
$d::id_session_init($conf);
