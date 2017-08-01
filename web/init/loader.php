<?php

$loader = new \Phalcon\Loader();
/**
 * We're a registering a set of directories taken from the configuration file
 */

$loader->registerNamespaces(
        array(
            'App\Controllers' => __DIR__ . "/../../controllers/",
            'App\Models' => __DIR__ . "/../../models/",
            'App\Plugins' => __DIR__ . "/../../plugins/",
            'App\Helper' => __DIR__ . "/../../lib/Helper/",
            'Httpful' => __DIR__ . "/../../lib/Httpful/",
            
        )
);

$loader->registerDirs(        
        array(
            __DIR__ . "/../../lib/"
        )
);

$loader->register();
