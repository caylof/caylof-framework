<?php

return [
    'author' => 'caylof',
    'mysql' => [
        'db_name' => 'test'
    ],
    'services' => [
        ['name' => 'pdo', 'provider' => '\Caylof\Db\PDO', 'singleton' => true]
    ]
];
