<?php

module::volatile();

if (!defined('PI')) define('PI', 3.1415926535897931);

/**
 * @class Math
 * @brief Math support functions
 *
 */
abstract class Math {

    /**
     * @brief Generate the specific number of primes
     *
     * @param Integer $n The number of primes to generate
     * @return Array The primes
     */
    public static function generatePrimes($n) {
        if ($n == 2) return array(2);
        if ($n < 2) return array();
        $s = range(3, $n + 1, 2);
        $mroot = $n ^ 0.5;
        $half = ($n + 1) / 2 - 1;
        $i = 0;
        $m = 3;
        while ($m <= $mroot) {
            if ($s[$i]) {
                $j = ($m * $m - 3) / 2;
                $s[$j] = 0;
                while ($j < $half) {
                    $s[$j] = 0;
                    $j += $m;
                }
            }
            $i++;
            $m = 2 * $i + 3;
        }
        $ret = array();
        foreach($s as $v) if ($v) $ret[] = $v;
        return $ret;
    }
    
    public function sum(Array $a) {
        return array_sum($a);
    }
    
    public function average(Array $a) {
        $sum = array_sum($a);
        $num = count($a);
        return ($sum / $num);
    }
    
    public function max(Array $a) {
        $max = null;
        foreach($a as $v) if ((!$max) || ($max < $v)) $max = $v;
        return $max;
    }
    
    public function min(Array $a) {
        $min = null;
        foreach($a as $v) if ((!$min) || ($min > $v)) $min = $v;
        return $min;
    }
    
    public function deviation(Array $a) {
        $max = abs(math::max($a));
        $min = abs(math::min($a));
        $avg = abs(math::average($a));
        return $avg - ($max + $min) / 2;
    }

}
