<?php

return array(
	'publicPath' => __DIR__ . '/../',
	'relativeUrl' => '/',
	'absoluteUrl' => 'http://daisywheel.local/',

    'components' => array(
        'db' => array(
            'dsn' => 'mysql:dbname=taxi;host=127.0.0.1',
            'username' => 'taxi',
            'password' => 'taxi',
        ),
    ),
);
