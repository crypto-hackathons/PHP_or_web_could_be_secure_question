<?php

trait Audit_simple {

  Use Sign_simple, Otp_simple;

  function audit_object(stdClass $object, string $sign_public_key, $otp_id = false):string {

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $data = json_encode($object);
    if($data === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);
    $object->out = new stdClass();

    if($otp_id !== false) {

      self::$otp_id = $otp_id;
      $object->out->hash = self::otp_hash($data);
    }
    else $object->out->hash = self::hash($data);

    $data = json_encode($object);
    if($data === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $object->out->out = new stdClass();
    $object->out->out->sign_public_key = $sign_public_key;
    $object->out->out->sign = self::sign($data);

    $data = json_encode($object);
    if($data === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $data_checksum = md5($data);
    $object->out->out->out = new stdClass();
    $object->out->out->out->checksum = $data_checksum;

    $data = json_encode($object);
    if($data === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $data = self::compress($data);

    return $data;
  }

  function audit_verify(string $data, $otp_id = false):stdClass {

    Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $data = self::uncompress($data);
    $object = json_decode($data);

    $object_check_checksum = $object;
    unset($object_check_checksum->out->out->out);

    $t = json_encode($object_check_checksum);
    if($t === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    if(md5() !== $object->out->out->out->checksum) Env::e('Checksum error', __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $object_check_sign = $object_check_checksum;
    unset($object_check_sign->out->out);

    $t2 = json_encode($object_check_sign);
    if($t2 === false) Env::e('Error Json encoding: '.json_last_error_msg(), __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    if(self::sign_verify($t2, $object->out->out->sign, $object->out->out->sign_public_key) === false) Env::e('Sign error', __CLASS__.'::'.__METHOD__.'::'.__LINE__);

    $object_check_hash = $object_check_sign;
    unset($object_check_hash->out);

    if($otp_id !== false) {

      self::$otp_id = $otp_id;
      if(self::otp_hash($object_check_hash) !== $object->out->hash) Env::e('Hash error', __CLASS__.'::'.__METHOD__.'::'.__LINE__);
    }
    else {

        if(self::hash($object_check_hash) !== $object->out->hash) Env::e('Hash error', __CLASS__.'::'.__METHOD__.'::'.__LINE__);
    }
    return $object_check_hash;
  }
}
