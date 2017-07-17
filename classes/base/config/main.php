<?php
return [
    'components' =>
    [
        'db' => [
            //'class' => 'Db',
            'class' => 'DbMysqli',
            'host' => 'localhost',
            'db' => 'guest_book',
            'user' => 'guest_book',
            'password' => 'guest_book',
            'port' => '3309',
        ],
        'request' => [
            'class' => 'Request',
            'controller' => 'main',
            'action' => 'index',
            'namespaceController' => '\classes\controllers'
        ]
    ]
];