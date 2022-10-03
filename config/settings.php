<?php

return [
    'settings' => [
        'displayErrorDetails' => false,
        'addContentLengthHeader' => false,
        'arhunt' => [
            'path' => '/arhunt',
            'website' => [
                'protocol' => 'https',
                'domain' => 'madales.altervista.org',
            ],
            'admin' => [
                'email' => 'madales@altervista.org',
                'name' => 'Madales',
            ],
        ],
        'logger' => [
            'name' => 'arhunt',
            'logs' => '../logs/arhunt.log',
        ],
        'db' => [
            'host' => 'localhost',
            'user' => 'madales',
            'pass' => '',
            'dbname' => 'my_madales',
        ],
        'storage' => [
            'storage' => '../storage',
            'path' => '/storage',
            'max_space' => 32 * 1024 * 1024,
        ],
        'view' => [
            'templates' => '../templates'
        ],
    ],
];