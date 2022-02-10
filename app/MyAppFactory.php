<?php

namespace App;

use Dzooli\Phalcon\Core\MicroAppFactory;

class MyAppFactory extends MicroAppFactory
{
    public function addRoutes()
    {
        $app = $this->app;
        $this->app->get('/', function () use ($app) {
            echo $app['view']->render('index');
        });
        return $this;
    }
}
