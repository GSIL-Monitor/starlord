<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: didi
 * Date: 2018/6/8
 * Time: 15:45
 */


define('COND_EQUAL',"COND_EQUAL");
define('COND_LESS', "COND_LESS");
define('COND_LARGER',"COND_LARGER");
define('COND_LESS_EQUAL',"COND_LESS_EQUAL");
define('COND_LARGER_EQUAL',"COND_LARGER_EQUAL");


if(!function_exists("compareFloat64")){
    /**
     * @param $number1 float left value
     * @param $number2 float right value
     * @param $expr string express for comparing
     * @return bool result
     */
    function  compareFloat64( $number1, $number2 , $expr )  {
        $result = false;
        switch ($expr) {
            case COND_EQUAL:
                $result = ($number1 == $number2);
                break;
            case COND_LARGER:
                $result = ($number1 > $number2);
                break;
            case COND_LESS:
                $result = ($number1 < $number2);
                break;
            case COND_LARGER_EQUAL:
                $result = ($number1 >= $number2);
                break;
            case COND_LESS_EQUAL:
                $result = ($number1 <= $number2);
                break;
        }
        return $result;
    }
}

if(!function_exists("betweenFloat64")){
    /**
     * @param $number number target value
     * @param $numberMin number min value
     * @param $numberMax number max value
     * @return bool
     */
    function  betweenFloat64($number, $numberMin, $numberMax )  {
        if ($numberMin > $numberMax) {
            list($numberMin, $numberMax) = array($numberMax, $numberMin);
        }
        return $number >= $numberMin && $number <= $numberMax;
    }
}

if(!function_exists("notBetweenFloat64")){
    /**
     * @param $number number target value
     * @param $numberMin number min value
     * @param $numberMax number max value
     * @return bool
     */
    function  notBetweenFloat64($number, $numberMin, $numberMax )  {
        return ! betweenFloat64($number, $numberMin, $numberMax ) ;
    }
}
