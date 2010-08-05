<?php

// application-infos
define('EXPLORER_NAME', 'Command EXplorer');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);

define('NL',  "\n");
define('TAB', "    ");

define('IS_AJAX', !empty($_POST['ajax']));
define('COMMAND', (empty($_GET['cmd']) ? null : $_GET['cmd']));
define('VIEW', (empty($_GET['cmd']) ? 'welcome' : 'command') . '.php');

define('COMMAND_DEPRECATED', 'java.lang.Deprecated');
define('COMMAND_SINGLETON', 'com.google.inject.Singleton');

define('DEBUG', E_ALL);

error_reporting(DEBUG);

require ROOT . 'libraries' . DS . 'Explorer.php';
require ROOT . 'libraries' . DS . 'Ajax.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Annotations.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'IpcCommand.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Package.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Params.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Returns.php';
require ROOT . 'libraries' . DS . 'objs' . DS . 'Throws.php';

Explorer::init($config);

if (IS_AJAX) {
    require ROOT . 'view' . DS . 'ajax.php';
} else {
    require ROOT . 'view' . DS . 'index.php';
}
 
Explorer::disconnect();

?>