<?php
/**
 *
 * 1. requires Palava.php to be loaded
 *
 */

// application-infos
define('EXPLORER_NAME', 'Command EXplorer');

// more constants
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

require 'libraries/Explorer.php';
require 'libraries/IpcCommand.php';
require 'libraries/Package.php';

$Explorer = new Explorer($config);

require 'view/index.php';
 
?>