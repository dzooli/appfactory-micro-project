<?php

namespace App;

use Dzooli\Phalcon\Core\AbstractAppFactory;
use Dzooli\Phalcon\Core\MicroAppFactory;
use Dzooli\Phalcon\Core\RouterDefinitionInterface;

class MyAppFactory extends MicroAppFactory implements RouterDefinitionInterface
{
    public function addRoutes(): AbstractAppFactory
    {
        $app = $this->app;
        $this->app->get('/', function () use ($app) {
            echo $app['view']->render('index');
        });
        return $this;
    }
}
