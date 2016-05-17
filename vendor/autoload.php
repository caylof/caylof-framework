<?php
/*
|--------------------------------------------------------------------------
| Autoload the vendor autoloadClass
|--------------------------------------------------------------------------
| also you can add other namespace that you want there
|
*/

require __DIR__.'/Psr4AutoloadClass.php';
$loader = new vendor\Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('caylof', __DIR__.'/caylof/src');

return $loader;
