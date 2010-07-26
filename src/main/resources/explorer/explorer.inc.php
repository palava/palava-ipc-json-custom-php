<?php
/**
 *
 * 1. requires Palava.php to be loaded
 *
 */

// application-infos
define('EXPLORER_NAME', 'Command EXplorer');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);

define('NL', '\n');
define('TAB', '\t');

define('VIEW', (empty($_GET['cmd']) ? 'welcome' : 'command') . '.php');
define('COMMAND', (empty($_GET['cmd']) ? null : $_GET['cmd']));

define('COMMAND_DEPRECATED', 'java.lang.Deprecated');
define('COMMAND_SINGLETON', 'com.google.inject.Singleton');

define('DEBUG', 1);

if (DEBUG == 0) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}

require ROOT . 'libraries' . DS . 'Explorer.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Annotations.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'IpcCommand.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Package.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Params.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Returns.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Throws.php';

Explorer::init($config);

require ROOT . 'view' . DS . 'index.php';
 
Explorer::disconnect();

?>