<?php

declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\View\Simple as View;
use Phalcon\Url as UrlResolver;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once(BASE_PATH . '/vendor/autoload.php');

abstract class AbstractAppFactory
{
    protected ?string $appPath = null;
    protected $di = null;
    protected ?\Phalcon\Config $appConfig = null;
    protected $loader = null;

    public function getConfig(): Phalcon\Config
    {
        return $this->appConfig;
    }

    public function getDi(): \Phalcon\Di\Di
    {
        return $this->di;
    }

    public function getPath()
    {
        return $this->appPath;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function initDefaults()
    {
        $this->initDi();
        $this->initBaseServices();
        $this->initConfig();
        $this->initLoader();
    }

    protected function initConfig(): void
    {
        $this->appConfig = $this->di->getShared('config');
    }
    protected function initBaseServices(): void
    {
        $factory = $this;
        $this->di->setShared('config', function () use ($factory) {
            return require $factory->appPath . "/config/config.php";
        });

        $this->di->setShared('view', function () {
            $config = $this->getConfig();

            $view = new View();
            $view->setViewsDir($config->application->viewsDir);
            return $view;
        });

        $this->di->setShared('url', function () {
            $config = $this->getConfig();

            $url = new UrlResolver();
            $url->setBaseUri($config->application->baseUri);
            return $url;
        });

        $this->di->setShared('db', function () {
            $config = $this->getConfig();

            $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
            $params = [
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname,
                'charset'  => $config->database->charset
            ];

            if ($config->database->adapter == 'Postgresql') {
                unset($params['charset']);
            }

            $connection = new $class($params);

            return $connection;
        });
    }

    protected function initDi(): void
    {
        $this->di = new FactoryDefault();
    }

    protected function initLoader(): void
    {
        $this->loader = new \Phalcon\Loader();

        $this->loader->registerDirs(
            [
                $this->appConfig->application->modelsDir,
                $this->appConfig->application->controllersDir,
                $this->appConfig->application->viewsDir,
            ]
        )->register();
    }
}

class MicroAppFactory extends AbstractAppFactory
{
    public function __construct(string $path)
    {
        $this->appPath = $path;
    }

    public function createApp(): Micro
    {
        $this->initDefaults();
        return new Micro($this->di);
    }
}

/**
 * Main program begins here
 */
try {
    $appFactory = new MicroAppFactory(APP_PATH);
    $app = $appFactory->createApp();

    /* Route tedinitions */
    include APP_PATH . '/app.php';

    /* Handle request */
    $app->handle($_SERVER['REQUEST_URI']);
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
