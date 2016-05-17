<?php
require __DIR__.'/boots.test.php';


// 测试\Caylof\Conf类
use caylof\Conf;

Conf::setFilePath(__DIR__.'/configs.test.php');

print_var(Conf::get());
print_var(Conf::get('author'));
print_var(Conf::get('mysql.db_name'));
