<?php

// Example bootstrap
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);

require( ROOT . '../palava/Palava.php');

$config['host'] = 'localhost';
$config['port'] = 2001;
$config['packages'] = array('de.cosmocode');
//$config['palavaConf']['example-configuration'] = 'example';

require( ROOT . 'explorer.inc.php');

?>