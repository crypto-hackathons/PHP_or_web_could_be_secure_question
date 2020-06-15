<?php 

class MerkleAttribut{
    
    public $merkleTree = array();
    public $merkleTreeRoot;
    public $position_list = array();
       
    public function __construct(array $infos){
        
        $hash_str = $this->list($infos);
                
        while (count($hash_str) > 2) {
            
            $hash_str = self::merkleTreeBinary($hash_str);
        }
        if (count($hash_str) === 1) {
            
            $this->merkleTree = $hash_str;
            $this->merkleTreeRoot = array_key_first($hash_str);
        }        
        if (count($hash_str) === 2) {
            
            $k1 = array_key_first($hash_str);
            $v1 = $hash_str[$k1];
            $k2 = array_key_last($hash_str);
            $v2 = $hash_str[$k2];
            $this->merkleTreeRoot = hash('sha256', $k1.$k2);
            
            $this->merkleTree[$this->merkleTreeRoot][$k1] = $v1;
            $this->merkleTree[$this->merkleTreeRoot][$k2] = $v2;
        }
        return true;
    }
    
     
    public function list(array $infos) {
        
        $hash_str = array();
        
        foreach($infos as $k => $v) {
            
            $index = hash('sha256',hash('sha256', $k).hash('sha256', $v));             
            $this->position_list[$k] = $index;
            
            $hash_str[$index] = array();
            $v1 = hash('sha256', $k);
            $v2 = hash('sha256', $v);
            $hash_str[$index][0] = $v1;
            $hash_str[$index][1] = $v2;
        }
        return $hash_str;
    }
    
    public static function merkleTreeBinary($hash_str){
        
        $hash_str2 = array();
        $c = 1;
        
        foreach($hash_str as $k => $v) {
            
            if($c === 1) {
                
                $v1 = $k;
                $vv1 = $v;
            }
            if($c === 2) {
                
                $v2 = $k;
                $vv2 = $v;
                $index = hash('sha256', $v1.$v2);
                $hash_str2[$index][$v1] = $vv1;
                $hash_str2[$index][$v2] = $vv2;
                $c = 0;
            }
            $c++;
        }
        if($c === 2) {
            
            $index = hash('sha256', $v1);
            $hash_str2[$index][$v1] = $vv1;
        }
        return $hash_str2;
    }
}

class MerkleTreeItem {

    public static $pubKeyFrom;
    public static $pubKeyTo;
    
    public $merkleTree = array();
    public $merkleTreeRoot;
    public $attribut;
    public $pubkey;
    public $sign;
    public $timestamp;
    
    public function __construct(array $infos, string $privkey){
        
        $merkleAttribut = new MerkleAttribut($infos);
        $this->attribut = $merkleAttribut;
        $this->pubkeyFrom = self::$pubKeyFrom;
        $this->pubkeyTo = self::$pubKeyTo;
        $this->timestamp = time();
        
        $infos = array();
        $infos['sign'] = $this->sign;
        $infos['timestamp'] = $this->timestamp;
        $merkleTreeLeft = new MerkleAttribut($infos);
        $merkleTreeRight = $this->attribut;
        
        $this->merkleTreeRoot = hash('sha256', $merkleTreeLeft->merkleTreeRoot.$merkleTreeRight->merkleTreeRoot);
        $this->merkleTree[$this->merkleTreeRoot][$merkleTreeLeft->merkleTreeRoot]  = $merkleTreeLeft->merkleTree;
        $this->merkleTree[$this->merkleTreeRoot][$merkleTreeRight->merkleTreeRoot]  = $merkleTreeRight->merkleTree;
    }
}