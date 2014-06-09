<?php

namespace daisywheel\web;

use daisywheel\core\ClassLoader;
use daisywheel\core\Context;
use daisywheel\core\Config;

class Application
{
    public static function run($appPath) {
        ClassLoader::create('app', $appPath);

        $context = new Context(Config::create("{$appPath}/config", 'web')->defaults(array(
            'appPath' => $appPath,
            'components' => array(
                'request' => array(
                    'class' => 'daisywheel\web\Request',
                ),
                'response' => array(
                    'class' => 'daisywheel\web\Response',
                ),
                'session' => array(
                    'class' => 'daisywheel\web\Session',
                ),
                'bootstrapper' => array(
                    'class' => 'app\bootstrapper',
                ),
            ),
        )));

        $context->bootstrapper->bootstrap();
        $context->bootstrapper->run();

        $context->response->flush();
    }
}
