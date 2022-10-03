<?php

$container = $app->getContainer();

$container['logger'] = function($container) {
    $settings = $container['settings']['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $fileHandler = new \Monolog\Handler\StreamHandler($settings['logs']);
    $logger->pushHandler($fileHandler);
    return $logger;
};

$container['db'] = function($container) {
    $settings = $container['settings']['db'];
    $db = new \PDO('mysql:host=' . $settings['host'] . ';dbname=' . $settings['dbname'],
        $settings['user'], $settings['pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
};

$container['storage'] = function($container) {
    $settings = $container['settings']['storage'];
    return new \Stdlib\Storage\Storage($settings['storage']);
};

$container['view'] = function($container) {
    $settings = $container['settings']['view'];
    $view = new \Slim\Views\Twig($settings['templates'], []);
    $view->addExtension(new \Slim\Views\TwigExtension($container['router'], null));
    $view->getEnvironment()->addGlobal('__settings', $container['settings']);
    return $view;
};