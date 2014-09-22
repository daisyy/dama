<?php
/**
 * @author: Jackong
 * Date: 14-9-22
 * Time: 下午5:51
 */
date_default_timezone_set("PRC");

define('APP_DIR', dirname(__DIR__));
define("TIME", $_SERVER['REQUEST_TIME']);
define("DATE", date("Ymd", TIME));
define("NOW", date("H:i:s", TIME));

function app_loader($class) {
    $namespace = "src\\";
    $length = strlen($namespace);
    if (substr($class, 0, $length) == $namespace) {
        require_once APP_DIR . '/' . str_replace("\\", "/", $class) . '.php';
    }
}

spl_autoload_register('app_loader');