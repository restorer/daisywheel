<?php

namespace daisywheel\web;

use daisywheel\core\ClassLoader;
use daisywheel\core\Context;
use daisywheel\core\Config;

class Application
{
    /*

    Варианты DI:

    $app = $context->createComponent('application', [
        'request' => new MyMockRequest(),
    ]);

    ----

    public function __construct()
    {
        $this->depends([
            'request' => [
                'class' => 'daisywheel\web\Request',
            ],
            'response' => [
                'class' => 'daisywheel\web\Response',
            ],
            'session' => [
                'class' => 'daisywheel\web\Session',
            ],
            'bootstrapper' => [
                'class' => 'app\bootstrapper',
            ],
        ]);
    }

    public static function init($config)
    {
        $this->request->...();
    }

    ----

    protected function getRequest()
    {
        return $this->getDependency('request', [
            'class' => 'daisywheel\web\Request',
        ]);
    }

    protected function getResponse()
    {
        return $this->getDependency('response', [
            'class' => 'daisywheel\web\Response',
        ]);
    }

    ...

    public static function init($config)
    {
        $this->request->...();
    }

    */

    public static function run($appPath)
    {
        ClassLoader::create('app', $appPath);

        $context = new Context(Config::create("{$appPath}/config", 'web')->defaults([
            'appPath' => $appPath,
            'components' => [
                'request' => [
                    'class' => 'daisywheel\web\Request',
                ],
                'response' => [
                    'class' => 'daisywheel\web\Response',
                ],
                'session' => [
                    'class' => 'daisywheel\web\Session',
                ],
                'bootstrapper' => [
                    'class' => 'app\bootstrapper',
                ],
            ],
        ]));

        $context->bootstrapper->bootstrap();
        $context->bootstrapper->run();

        $context->response->flush();
    }
}
