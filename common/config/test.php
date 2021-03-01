<?php
$params = array_merge(
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-common-tests',
    'basePath' => dirname(__DIR__),
    'params' => $params,
    'components' => [
        'db' => [
            'dsn' => 'pgsql:host=localhost;dbname=team_analisys_test',
            'username' => 'test',
            'password' => 'test',
        ],
    ],
];
