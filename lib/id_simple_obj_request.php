<?php

class Request {

      public static function info_from_request(string $name, array $source){

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $name);

        if(isset($source[$name]) === true && empty($source[$name]) === false) return urldecode(strip_tags($source[$name]));
      }

      public static function info_from_post(string $name){

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $name);

        return self::info_from_request($name, $_POST);
      }

      public static function info_from_get(string $name){

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $name);

        return self::info_from_request($name, $_GET);
      }

      public function info_from_post_request(string $name) {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $name);

         $this->$name = self::info_from_post($name);

         return $this->$name;
      }

      public function info_from_get_request(string $name):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $name);

         $this->$name = self::info_from_get($name);

         return $this->$name;
      }

      public function __construct(){

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        foreach($this as $k => $v) {

            self::info_from_post_request($k);
        }
      }
}
