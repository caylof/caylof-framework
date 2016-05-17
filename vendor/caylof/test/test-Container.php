<?php
require __DIR__.'/boots.test.php';

use caylof\Container;


class Foo {

}

class Bar {

}


$di = new Container();

// 两种组件注入方式
$di->set('foo', function() {
    return new Foo();
});

$di->bar = function() {
    return new Bar();
};

$di->other = function() {
    return ['other'];
};

// 两种组件获取方式
print_var($di->get('foo'));
print_var($di->bar);
print_var($di->get('other'));
print_var($di->getShared('foo'));
print_var($di->getShared('foo'));
print_var($di->foo);


// 获取不存在的组件会抛出例外
//print_var($di->none);
