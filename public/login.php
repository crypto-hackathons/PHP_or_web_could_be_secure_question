<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$node_id_hash = ';'

require_once('../lib/compress_simple.php');
require_once('../lib/rsa_simple.php');
require_once('../lib/cert_simple.php');
require_once('../lib/pgp_simple.php');
require_once('../lib/sign_simple.php');
require_once('../lib/seed_simple.php');
require_once('../lib/hash_simple.php');
require_once('../lib/id_simple.php');
require_once('../lib/crypto_simple.php');
require_once('../lib/merkle_tree_simple.php');


function error(string $error){
    
    header("location: login.php?error=".$error);
}
class Distribute {
    
    use Crypto_simple;
    
}
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

$conf['month_list'] = $month_list;
$conf['day_list'] = $day_list;

$node = $d->id_get($node_id_hash);

if(isset($_GET['sessionId']) === true ) {
    
    $sessionId = urldecode(strip_tags($_GET['sessionId']));    
    $session = $d->id_get($sessionId);
}
else {    
    
    $session = $d->id_create($node->conf, $node->name.uniqid().time.rand(0, 100000), $node->emailAddress, $node->telNumber, $node->password, $node->pgp_passphrase);
}

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = urldecode(strip_tags($_POST['username']));
    $password = urldecode(strip_tags($_POST['password']));
    
    $file = '../data/user/'.hash('sha256', 'prefix'.$username.$password).'.json';
    
    if(is_file($file) === false) {
        
        error('Bad user or password.');
    }
    
    file_get_contents(hash('sha256', 'prefix'.$username.$password));
    
    $sql = "SELECT id FROM admin WHERE username = '$myusername' and passcode = '$mypassword'";
    $result = mysqli_query($db,$sql);
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    $active = $row['active'];
    
    $count = mysqli_num_rows($result);
    
    // If result matched $myusername and $mypassword, table row must be 1 row
    
    if($count == 1) {
        session_register("myusername");
        $_SESSION['login_user'] = $myusername;
        
        header("location: welcome.php");
    }else {
        $error = "Your Login Name or Password is invalid";
    }
}