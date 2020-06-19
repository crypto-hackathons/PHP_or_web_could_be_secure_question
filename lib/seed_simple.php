<?php

trait Seed_simple
{
    public static $seed_dir = 'seed';
    public static $word_dir = 'wordlists';
    public static $seed_grain_file = 'grain.txt';
    public static $SEED_CHINESE_SIMPLIFIED_WORDLIST_FILE = 'chinese_simplified.json';
    public static $SEED_CHINESE_TRADITIONAL_WORDLIST_FILE = 'chinese_traditional.json';
    public static $SEED_ENGLISH_WORDLIST_FILE = 'english.json';
    public static $SEED_FRENCH_WORDLIST_FILE = 'french.json';
    public static $SEED_ITALIAN_WORDLIST_FILE = 'italian.json';
    public static $SEED_JAPANESE_WORDLIST_FILE = 'japanese.json';
    public static $SEED_KOREAN_WORDLIST_FILE = 'korean.json';
    public static $SEED_SPANISH_WORDLIST_FILE = 'spanish.json';
    public static $SEED_DEFAULT_WORDLIST_FILE = 'english.json';
    private static $SEED_INVALID_MNEMONIC = 'Invalid mnemonic';
    private static $SEED_INVALID_ENTROPY = 'Invalid entropy';
    private static $SEED_INVALID_CHECKSUM = 'Invalid mnemonic checksum';
    private static $seed_entropy_algo = 'sha512'; // 128 bits
    public static $seed_word_list;
    public static $seed_mnemonic;
    public static $seed_hash_prefix;

    public static function seed_init(string $wordlist_file):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        self::$seed_hash_prefix = hash('sha256', Env::file_get_contents(self::$seed_grain_file));
        self::$seed_word_list = Env::file_get_contents_json(self::$word_dir.'/'.$wordlist_file.'.json');

        return true;
    }

    public static function seed_private_key_master_str_get():array {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $grain = self::seed_grain_get();
        $rsa_private_key = self::rsa_private_key_get();
        $private_key_master = hash(self::$seed_entropy_algo, $rsa_private_key . $grain, true);
        $private_key_master_bin_str = sprintf("%128b", $private_key_master);
        $checksum_str = substr($private_key_master_bin_str, -4);

        $seed_private_key_master_str = new stdClass();
        $seed_private_key_master_str->private_key_master = $private_key_master_bin_str;
        $seed_private_key_master_str->checksum = $checksum_str;
        $seed_private_key_master_str->rsa_private_key_list = array();

        $keys = new stdClass();
        $keys->private = $rsa_private_key;
        $keys->public = self::rsa_public_key_get();
        $seed_private_key_master_str->rsa_private_key_list[] = $keys;

        return $seed_private_key_master_str;
    }

    private static function seed_mnemonic_file_write(stdclass $seed_private_key_master_str):string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $file = self::$seed_private_key_master_dir.'/'.$seed_private_key_master_str->private_key_master.'_private_key_master.key';
        $data = json_encode($seed_private_key_master_str);

        if($data === false) Env::e('Error Json encoding');

        Env::file_put_contents($file, $data);

        return $data;
    }

    private static function seed_mnemonic_file_read(string $private_key_master):stdClass {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $file = self::$seed_private_key_master_dir.$private_key_master.'_private_key_master.key';
        $json_obj = Env::file_get_contents_json($file);

        return $json_obj;
    }

    public static function seed_mnemonic_gen():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $mnemonic = array();
        $seed_private_key_master_str = self::seed_private_key_master_str_get();

        list($private_key_master) = sscanf($seed_private_key_master_str->private_key_master, '%128b');
        list($checksum) = sscanf($seed_private_key_master_str->checksum, '%04b');
        $entropy132bits = $private_key_master . $checksum;

        $entropy132bits_bin_str = sprintf("%132b", $entropy132bits);
        $entropy132bits_bin_str_parts = str_split($entropy132bits_bin_str, 11);

        foreach ($entropy132bits_bin_str_parts as $entropy132bits_bin_str_part) {

            list($index_bin) = sscanf($entropy132bits_bin_str_part, '%11b');
            $index_dec = bindec($index_bin);
            $mnemonic[] = self::word_list[$index_dec];
        }
        $mnemonic_str = implode(' ', $mnemonic);

        self::seed_mnemonic_set($mnemonic_str);

        self::seed_mnemonic_file_write($seed_private_key_master_str);

        return self::mnemonic;
    }

    private static function seed_grain_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, self::$seed_grain_file);

        $grain = Env::file_get_contents(self::$seed_grain_file);

        return $grain;
    }

    public static function seed_mnemonic_get():string {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        return self::seed_mnemonic;
    }

    public static function seed_mnemonic_set(string $mnemonic):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        self::$seed_mnemonic = $mnemonic;

        return true;
    }

    public static function seed_private_key_restore_control(string $private_key_master_str_test, string $checksum_str_test):bool {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        $seed_private_key_master = self::seed_private_key_master_str_get();

        if($seed_private_key_master->private_key_master  === $private_key_master_str_test && $seed_private_key_master->checksum === $checksum_str_test) {

            return true;
        }
        return false;
    }

    public static function seed_private_key_restore(string $mnemonic):stdClass {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__);

        self::seed_mnemonic_set($mnemonic);

        $word_bin_str = '';
        $mnemonic_array = explode(' ', self::seed_mnemonic);

        foreach ($mnemonic_array as $word) {

            $index_dec = in_array($word, self::seed_word_list);
            $index_bin = decbin($index_dec);
            $word_bin_str .= sprintf("%11b", $index_bin);
        }
        $private_key_master_str = substr($word_bin_str, 0, -4);
        $checksum_str = substr($word_bin_str, -4);
        $control = self::seed_private_key_restore_control($private_key_master_str, $checksum_str);

        if($control === false) return false;

        $json = self::seed_mnemonic_file_read($private_key_master_str);

        return $json;
    }

    public static function seed_init_key_dir(string $n) {

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $n);

        $dir = Env::dir_create(self::$seed_dir, $n);
        Env::dir_create(self::$seed_dir.self::$word_dir, $n);

        $data = Env::file_get_contents(self::$seed_dir.'/'.self::$seed_grain_file);

        Env::l(__CLASS__.'::'.__METHOD__.'::'.__LINE__, $data);

        self::$seed_grain_file = self::$seed_dir.'/'.$n.'/'.basename(self::$seed_grain_file);

        Env::file_put_contents(self::$seed_grain_file, $data);

    }
}
