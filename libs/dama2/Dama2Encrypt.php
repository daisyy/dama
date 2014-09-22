<?php

class Dama2Encrypt{

    public static function encrypt($str, $key){
        if(strlen($key) != '32')
            throw new Exception("加密key必须为32位字符串", 1);
            
        $key8 = self::key16tokey8(self::hex2bin($key));
        return self::encrypt_($str, $key8);
    }

    public static function decrypt($str, $key){
        if(strlen($key) != '32')
            throw new Exception("加密key必须为32位字符串", 1);

        $key8 = self::key16tokey8(self::hex2bin($key));
        return self::decrypt_($str, $key8);
    }

    private static function hex2bin($str){
        $res = '';
        $strlen = strlen($str);
        if( $strlen % 2 == 1 )
            throw new Exception("Error String is Passed", 1);

        foreach( explode("\n", trim(chunk_split($str,2))) as $v){
            $res .= chr(hexdec($v));
        }
        return $res;
    }

    private static function key16tokey8($key16){
        $key8 = '';
        for($i = 0; $i < 8; $i++){
            $key8 .= ($key16{$i} ^ $key16{$i + 8});
        }
        return $key8;
    }

    private static function encrypt_($str, $key)
    {
        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);

        return bin2hex(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));
    }

    private static function decrypt_($str, $key)
    {   
        $str = mcrypt_decrypt(MCRYPT_DES, $key, self::hex2bin($str), MCRYPT_MODE_ECB);

        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = ord($str[($len = strlen($str)) - 1]);
        return substr($str, 0, strlen($str) - $pad);
    }

}