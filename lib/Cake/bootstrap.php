<?php
define('TIME_START', microtime(true));
if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);
if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', E_USER_NOTICE);
error_reporting(E_ALL & ~E_DEPRECATED);

if (!defined('CAKE_CORE_INCLUDE_PATH')) define('CAKE_CORE_INCLUDE_PATH', dirname(dirname(__FILE__)));
if (!defined('CORE_PATH')) define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
if (!defined('WEBROOT_DIR')) define('WEBROOT_DIR', 'webroot');
define('CAKE', CORE_PATH . 'Cake' . DS);

if (!defined('APP')) define('APP', ROOT . DS . APP_DIR . DS);
define('APPLIBS', APP . 'Lib' . DS);
if (!defined('CSS')) define('CSS', WWW_ROOT . 'css' . DS);
if (!defined('JS')) define('JS', WWW_ROOT . 'js' . DS);
if (!defined('IMAGES')) define('IMAGES', WWW_ROOT . 'img' . DS);
if (!defined('TESTS')) define('TESTS', APP . 'Test' . DS);
if (!defined('TMP')) define('TMP', APP . 'tmp' . DS);
if (!defined('LOGS')) define('LOGS', TMP . 'logs' . DS);
if (!defined('CACHE')) define('CACHE', TMP . 'cache' . DS);
if (!defined('VENDORS')) define('VENDORS', ROOT . DS . 'vendors' . DS);
if (!defined('IMAGES_URL')) define('IMAGES_URL', 'img/');
if (!defined('CSS_URL')) define('CSS_URL', 'css/');
if (!defined('JS_URL')) define('JS_URL', 'js/');
require CAKE . 'basics.php';
require CAKE . 'Core' . DS . 'App.php';
require CAKE . 'Error' . DS . 'exceptions.php';

spl_autoload_register(array('App', 'load'));

App::uses('ErrorHandler', 'Error');
App::uses('Configure', 'Core');
App::uses('CakePlugin', 'Core');
App::uses('Cache', 'Cache');
App::uses('Object', 'Core');
App::uses('Multibyte', 'I18n');

App::$bootstrapping = true;

if (!defined('FULL_BASE_URL')) {
    $s = null;
    if (env('HTTPS')) {
        $s = 's';
    }

    $httpHost = env('HTTP_HOST');

    if (isset($httpHost)) {
        define('FULL_BASE_URL', 'http' . $s . '://' . $httpHost);
        Configure::write('App.fullBaseUrl', FULL_BASE_URL);
    }
    unset($httpHost, $s);
}

Configure::write('App.imageBaseUrl', IMAGES_URL);
Configure::write('App.cssBaseUrl', CSS_URL);
Configure::write('App.jsBaseUrl', JS_URL);

Configure::bootstrap(isset($boot) ? $boot : true);

if (function_exists('mb_internal_encoding')) {
    $encoding = Configure::read('App.encoding');
    if (!empty($encoding)) {
        mb_internal_encoding($encoding);
    }
    if (!empty($encoding) && function_exists('mb_regex_encoding')) {
        mb_regex_encoding($encoding);
    }
}

if (!function_exists('mb_stripos')) {
    function mb_stripos($haystack, $needle, $offset = 0, $encoding = null) {
        return Multibyte::stripos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_stristr')) {
    function mb_stristr($haystack, $needle, $part = false, $encoding = null) {
        return Multibyte::stristr($haystack, $needle, $part);
    }
}

if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null) {
        return Multibyte::strlen($string);
    }
}

if (!function_exists('mb_strpos')) {
    function mb_strpos($haystack, $needle, $offset = 0, $encoding = null) {
        return Multibyte::strpos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_strrchr')) {
    function mb_strrchr($haystack, $needle, $part = false, $encoding = null) {
        return Multibyte::strrchr($haystack, $needle, $part);
    }
}

if (!function_exists('mb_strrichr')) {
    function mb_strrichr($haystack, $needle, $part = false, $encoding = null) {
        return Multibyte::strrichr($haystack, $needle, $part);
    }
}

if (!function_exists('mb_strripos')) {
    function mb_strripos($haystack, $needle, $offset = 0, $encoding = null) {
        return Multibyte::strripos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_strrpos')) {
    function mb_strrpos($haystack, $needle, $offset = 0, $encoding = null) {
        return Multibyte::strrpos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_strstr')) {
    function mb_strstr($haystack, $needle, $part = false, $encoding = null) {
        return Multibyte::strstr($haystack, $needle, $part);
    }
}

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($string, $encoding = null) {
        return Multibyte::strtolower($string);
    }
}

if (!function_exists('mb_strtoupper')) {
   function mb_strtoupper($string, $encoding = null) {
        return Multibyte::strtoupper($string);
    }
}

if (!function_exists('mb_substr_count')) {
    function mb_substr_count($haystack, $needle, $encoding = null) {
        return Multibyte::substrCount($haystack, $needle);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null) {
        return Multibyte::substr($string, $start, $length);
    }
}

if (!function_exists('mb_encode_mimeheader')) {
    function mb_encode_mimeheader($str, $charset = 'UTF-8', $transferEncoding = 'B', $linefeed = "\r\n", $indent = 1) {
        return Multibyte::mimeEncode($str, $charset, $linefeed);
    }
}

function dump( $var ) {
    echo '<pre>';
    if( is_object( $var ) || is_array( $var ) ) {
        print_r( $var );
    } else {
        echo $var;
    }
    echo '</pre>';
}

function array_insert( $array, $data, $position = null ) {
    if( $position < count( $array ) ) {
        $result = array_slice( $array, 0, $position );
        $result[] = $data;
        $result = array_merge( $result, array_slice( $array, $position ) );
    } else {
        $result = $array;
        $result[] = $data;
    }
    
    return $result;
}

function array_orderby() {
    $args = func_get_args();
    $data = array_shift( $args );
 
    if ( ! is_array( $data ) ) {
        return array();
    }
    $multisort_params = array();
    foreach ( $args as $n => $field ) {
        if ( is_string( $field ) ) {
            $tmp = array();
            foreach ( $data as $row ) {
                $tmp[] = $row[ $field ];
            }
            $args[ $n ] = $tmp;
        }
        $multisort_params[] = &$args[ $n ];
    }
 
    $multisort_params[] = &$data;
    call_user_func_array( 'array_multisort', $multisort_params );
    return end( $multisort_params );
}

function get_visitor_ip(){
    $ip = "UNKNOWN";
    if ( getenv( "HTTP_CLIENT_IP" ) ) {
        $ip = getenv( "HTTP_CLIENT_IP" );
    } elseif( getenv( "HTTP_X_FORWARDED_FOR" ) ) {
        $ip = getenv( "HTTP_X_FORWARDED_FOR" );
    } elseif( getenv( "REMOTE_ADDR" ) ) {
        $ip = getenv( "REMOTE_ADDR" );
    }
    return $ip; 
}

function array_pluck(array $array, $field) {
    $final = array();
    foreach($array as $v) {
        $final[] = $v[$field];
    }
    return $final;
}

function divide( $numerator, $denominator, $precision = 2 ) {
    if( (float) $denominator == 0 || (float) $numerator == 0 ) return number_format( 0, $precision );
    return number_format($numerator / $denominator, $precision);
}

function fush_id($in, $to_num = false, $pad_up = false, $pass_key = null) {
    $out   = '';
    $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base  = strlen($index);

    if ($to_num) {
        $len = strlen($in) - 1;
        for ($t = $len; $t >= 0; $t--) {
            $bcp = bcpow($base, $len - $t);
            $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
        }
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $out -= pow($base, $pad_up);
            }
        }
    } else {
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $in += pow($base, $pad_up);
            }
        }
        for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
            $bcp = bcpow($base, $t);
            $a   = floor($in / $bcp) % $base;
            $out = $out . substr($index, $a, 1);
            $in  = $in - ($a * $bcp);
        }
    }
    return $out;
}

function toDb( $date, $addTime = false ) {
    if( $addTime ) return date( 'Y-m-d H:i:s', strtotime( $date ) );
    else return date( 'Y-m-d', strtotime( $date ) );
}

function toUI( $date ) {
    return date( 'm/d/Y', strtotime( $date ) );
}

function createId( $intOnly = true ) {
    $fixed = 1390000000;
    $t = explode( " ", microtime() );
    $uid = $t[1] - $fixed;
    return ( $intOnly ) ? $uid : uniqid();
}

function array_multi_search($array, $key, $value) {
    $results = array();
    if( is_array( $array ) ) {
        if( isset( $array[$key] ) && $array[$key] == $value ) {
            $results[] = $array;
        }
        foreach ( $array as $subarray ) {
            $results = array_merge( $results, array_multi_search( $subarray, $key, $value ) );
        }
    }
    return $results;
}