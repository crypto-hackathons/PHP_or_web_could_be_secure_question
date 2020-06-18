<?php

class Hash_simple_obj_array_hashed {

    use Hash_simple;

   public $to_share = array();
   public $to_store = array();

   public function __construct(array $array, $hash_prefix = false) {

         $keys = array_keys($array);

         shuffle($keys);

         foreach($keys as $key) {

             $this->to_share[$key] = $array[$key];
         }
         foreach ($new as $k => $v) $this->to_store[$k] = self::hash($v, $hash_prefix);
   }
}
