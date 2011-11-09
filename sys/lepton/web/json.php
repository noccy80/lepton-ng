<?php

/**
 * @class Json
 * @brief Decodes JSON chunks as arrays rather than stdclasses
 * 
 * @author Christopher Vagnetoft <noccy.com>
 */
class Json {

    /**
     * @brief Decode a JSON chunk
     * 
     * @param String $json The JSON data to process
     * @return Mixed The parsed data
     */
    public static function decode($json) {
        return self::decodeRecursive(json_decode($json));
    }
    
    /**
     * @brief Recursive decoder
     * @private
     * 
     * @param String $json The JSON data to process
     * @return Mixed The parsed data
     */
    private static function decodeRecursive($json) {
        $arr = (array)$json;
        foreach($arr as $k=>$v) {
            if ((typeOf($v) == 'stdClass') || typeOf($v) == 'array') { $arr[$k] = (array)self::decodeRecursive($v); }
        }
        return $arr;
    }
    
}
