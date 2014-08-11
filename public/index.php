<?php
ini_set('display_errors', true);

set_time_limit(0);
//ini_set('memory_limit', '128M');
ini_set('memory_limit', '-1');

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
