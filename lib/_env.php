<?php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('../lib/rsa_simple.php');
require_once('../lib/cert_simple.php');
require_once('../lib/compress_simple.php');
require_once('../lib/crypto_simple.php');
require_once('../lib/pgp_simple.php');
require_once('../lib/sign_simple.php');
require_once('../lib/seed_simple.php');
require_once('../lib/hash_simple.php');
require_once('../lib/otp_simple.php');
require_once('../lib/audit_simple.php');
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

    public static $conf_file = 'conf/global.json';
    public static $dir_root = '.';
    public static $data_dir_global = '../data';
    public static $data_dir_global_right = 0777; // @TODO /!\ to change

    public static function conf():stdClass {

        $conf = self::file_get_contents_json(self::$conf_file);

        $display_errors = '0';
        $display_startup_errors = '0';
        $error_reporting = E_ERROR;

        if($conf->display_errors) $display_errors = '1';
        ini_set('display_errors', $display_errors);

        if($conf->display_startup_errors) $display_startup_errors = '1';
        ini_set('display_startup_errors', $display_startup_errors);

        if($conf->error_reporting === '*') $error_reporting = E_ALL;
        error_reporting($error_reporting);

        return $conf;
    }

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

    public static function file_put_contents(string $file, string $data):int {

        self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

        $file = str_replace('../', '', $file);
        $file = str_replace('./', '', $file);

        return file_put_contents(self::$dir_root.'/'.self::$data_dir_global.'/'.$file, $data);
    }

    public static function file_get_contents(string $file):string {

        self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

        $file = str_replace('../', '', $file);
        $file = str_replace('./', '', $file);

        $file = self::$dir_root.'/'.self::$data_dir_global.'/'.$file;

        if(is_file($file) === false) self::e('File not found');

        return file_get_contents(self::$dir_root.'/'.self::$data_dir_global.'/'.$file);
    }

    public static function file_get_contents_json(string $file) {

        self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $file);

        $file = str_replace('../', '', $file);
        $file = str_replace('./', '', $file);

        return json_decode(self::file_get_contents($file));
    }

    static function e(string $error, string $context, string $error_redirect = 'location: login.php?error='){

        self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $error);
        self::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $context);

        header($error_redirect.$error);
        exit;
    }

    static function l(string $msg, $info = '', string $eol = '<br>') {

        $i = json_encode($info);

        if($i === false) {

            $info = '{ "sys"="Error Json encode: '.json_last_error_msg().'"}';
        }
        echo time().' --- '.$msg.' --- '.json_encode($info).$eol;
    }
}

class Distribute {

    use Id_simple;
}
$conf = Env::conf();
$d = new Distribute();
$d::id_session_init($conf);
