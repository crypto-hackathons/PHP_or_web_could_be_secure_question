<?php 

/**
 * Trait Hash_simple
 */
Trait Hash_simple {
        
    use Seed_simple;
    
    public static $hash_algo = 'sha256';
    public static $hash_prefix;
    
    public static function hash_init(){
        
        self::$hash_prefix = hash(self::$hash_algo, self::file_get_contents(self::$seed_grain_file));
    }
    
    public static function hash(string $data, $hash_prefix = false){
        
        if($hash_prefix === false) $hash_prefix = self::$hash_prefix;

        return hash(self::$hash_algo, $hash_prefix.$data);
    }
    
    public static function hash_array(array $array, $hash_prefix = false){
        
        $array_hashed = new stdClass();
        shuffle($array);
        $array_hashed->to_share = $array;
        foreach ($array as $k => $v) $array_hashed->to_store[$k] = self::hash($v, $hash_prefix);
        
        return $array_hashed;
    }
}
