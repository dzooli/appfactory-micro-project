<?php

declare(strict_types=1);
error_reporting(E_ALL);

use App\MyAppFactory;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once(BASE_PATH . '/vendor/autoload.php');

try {
    $appFactory = new MyAppFactory(APP_PATH);
    $appFactory->createApp()
        ->addRoutes()
        ->getApp()
        ->handle($_SERVER['REQUEST_URI']);
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}