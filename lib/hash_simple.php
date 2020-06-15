<?php 

/**
 * Trait Hash_simple
 */
Trait Hash_simple {
        
    use Seed_simple;
    
    public static $hash_prefix;
    
    public static function hash_init(){
        
        self::$seed_hash_prefix = hash('sha256', self::file_get_contents(self::$seed_grain_file));
    }
    
    public static function hash(string $data){
        
        return hash('sha256', self::$hash_prefix.$data);
    }
    
    public static function hash_array(array $array){
        
        $array_hashed = new stdClass();
        shuffle($array);
        $array_hashed->to_share = $array;
        foreach ($array as $k => $v) $array_hashed->to_store[$k] = hash('sha256', self::$hash_prefix.$v);
        
        return $array_hashed;
    }
}