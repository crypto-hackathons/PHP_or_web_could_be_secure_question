<?php

trait Audit_simple {

  Use Sign_simple, Otp_simple;

  function audit_object(stdClass $object, string $sign_public_key, $otp_id = false):string {

    $data = json_encode($object);
    $object->out = new stdClass();

    if($otp_id !== false) {

      self::$otp_id = $otp_id;
      $object->out->hash = self::otp_hash($data);
    }
    else $object->out->hash = self::hash($data);

    $data = json_encode($object);
    $object->out->out = new stdClass();
    $object->out->out->sign_public_key = $sign_public_key;
    $object->out->out->sign = self::sign($data);
    $data = json_encode($object);
    $data_checksum = md5($data);
    $object->out->out->out = new stdClass();
    $object->out->out->out->checksum = $data_checksum;
    $data = json_encode($object);
    $data = self::compress($data);

    return $data;
  }

  function audit_verify(string $data, $otp_id = false):stdClass {

    $data = self::uncompress($data);
    $object = json_decode($data);

    $object_check_checksum = $object;
    unset($object_check_checksum->out->out->out);

    if(md5(json_encode($object_check_checksum)) !== $object->out->out->out->checksum) error('Checksum error');

    $object_check_sign = $object_check_checksum;
    unset($object_check_sign->out->out);

    if(self::sign_verify(json_encode($object_check_sign), $object->out->out->sign, $object->out->out->sign_public_key) === false) error('Sign error');

    $object_check_hash = $object_check_sign;
    unset($object_check_hash->out);

    if($otp_id !== false) {

      self::$otp_id = $otp_id;
      if(self::otp_hash($object_check_hash) !== $object->out->hash) error('Hash error');
    }
    else {

      if(self::hash($object_check_hash) !== $object->out->hash) error('Hash error');
    }
    return $object_check_hash;
  }
}
