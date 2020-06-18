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

function error(string $error){

    header("location: login.php?error=".$error);
    exit;
}
class Distribute {

    use Crypto_simple;
}
$conf = json_decode(file_get_contents('../data/conf/global.json'));
var_dump($conf);
exit();
$d = new Distribute();
$d::id_session_init($conf);
