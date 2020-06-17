<?php

/**
 * Trait Hash_simple
 */
Trait Hash_simple {

    use Seed_simple;

    public static $hash_algo = 'sha256';
    public static $hash_prefix;

    public static function hash_init():bool {

        self::$hash_prefix = hash(self::$hash_algo, self::file_get_contents(self::$seed_grain_file));

        return true;
    }

    public static function hash(string $data, $hash_prefix = false):string {

        if($hash_prefix === false) $hash_prefix = self::$hash_prefix;

        return hash(self::$hash_algo, $hash_prefix.$data);
    }

    public static function hash_array(array $array, $hash_prefix = false):Hash_simple_obj_array_hashed {

        if($hash_prefix === false) $hash_prefix = self::$hash_prefix;

        return new Hash_simple_obj_array_hashed($array, $hash_prefix);
    }
}
