<?php

require __DIR__.'/boots.test.php';

use caylof\App;
use caylof\db\PDO;
use caylof\db\SqlBuilder;


$app = App::getInstance();

$app->setSingleton('pdo', function() {
    $dsn = 'mysql:host=localhost;dbname=test';
    $username = 'root';
    $password = '';
    return new PDO($dsn, $username, $password);
});

$app->sqlBuilder = function() {
    return new SqlBuilder();
};


$sqlBuilder = $app->sqlBuilder
->select()
->table('user')
//->join('inner', 'role', 'user.role_id = role.id')
//->where(['id' => 1])
//->whereRaw('name LIKE :name', ['name' => '%sd%'])
//->orderBy('name DESC')
->orderBy('id DESC')
->limit(0, 10)
->build();

//$res = $sqlBuilder->runBy($app->pdo)->fetch();
$res = $app->pdo->queryWith($sqlBuilder)->fetch();

print_var($res);
