<?php

spl_autoload_register(function ($class) {

		$namespaces = array(
			'DataFeed' => '/src/',
			'Pimple'   => '/modules/Pimple/src/'
		);

		foreach ($namespaces as $ns => $dir) {

			if (strpos($class, "$ns\\") === 0) {
				require_once __DIR__ . $dir . str_replace('\\', '/', $class) . '.php';
				break;
			}

		}

	});
