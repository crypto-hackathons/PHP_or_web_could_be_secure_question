<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../lib/rsa_simple.php');
require_once('../lib/cert_simple.php');
require_once('../lib/pgp_simple.php');
require_once('../lib/sign_simple.php');
require_once('../lib/seed_simple.php');
require_once('../lib/hash_simple.php');
require_once('../lib/opt_simple.php');
require_once('../lib/audit_simple.php');
require_once('../lib/id_simple.php');
require_once('../lib/crypto_simple.php');
require_once('../lib/merkle_tree_simple.php');

require_once('../lib/id_simple_obj_id.php');
require_once('../lib/id_simple_obj_request.php');
require_once('../lib/id_simple_obj_reques_from_id.php');
require_once('../lib/id_simple_obj_reques_from_otp.php');
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
$node_id_hash = ';';
$d = new Distribute();

$month_list[1] = 'Janvier';
$month_list[2] = 'Février';
$month_list[3] = 'Mars';
$month_list[4] = 'Avril';
$month_list[5] = 'Mai';
$month_list[6] = 'Juin';
$month_list[7] = 'Juillet';
$month_list[8] = 'Aout';
$month_list[9] = 'Septembre';
$month_list[10] = 'Octobre';
$month_list[11] = 'Novembre';
$month_list[12] = 'Décembre';

$day_list[1] = 'Lundi';
$day_list[2] = 'Mardi';
$day_list[3] = 'Mercredi';
$day_list[4] = 'Jeudi';
$day_list[5] = 'Vendredi';
$day_list[6] = 'Samedi';
$day_list[7] = 'Dimanche';

$conf = new stdClass();
$conf->month_list = $month_list;
$conf->day_list = $day_list;
