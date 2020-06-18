<?php

class Request {

      public static function info_from_post_request(string $name):string {

         if(isset($_POST[$name]) === true && empty($_POST[$name]) === false) $hihs->$name = urldecode(strip_tags($_POST[$name]));
      }

      public static function info_from_get_request(string $name):string {

         if(isset($_POST[$name]) === true && empty($_POST[$name]) === false) $hihs->$k = urldecode(strip_tags($_GET[$name]));
      }

      public function __construct(){

        foreach($this as $k => $v) {

            self::id_info_from_post_request($k);
        }

      }
}
