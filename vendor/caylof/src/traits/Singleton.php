<?php
namespace caylof\traits;

trait Singleton {

    private static $instance;

    private function __construct() {}
   
    public static function getInstance() {
        return self::$instance ?: self::$instance = new self();
    }
   
    private function __clone() {}
   
    private function __wakeup() {}
}
