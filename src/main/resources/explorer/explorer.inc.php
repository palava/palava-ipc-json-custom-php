<?php
/**
 *
 * 1. requires the following variables to be set
 *
 *  $config['host'] = 'localhost'
 *  $config['post'] = 2001
 *  $config['packages'] = array('de.cosmocode', '...')
 *
 *
 * 2. requires Palava.php to be loaded
 *
 */

// the commands command to get all available commands
define('COMMANDS', 'de.cosmocode.palava.ipc.json.custom.php.explorer');

// connect to palava
$palava = new Palava($config['host'], $config['port']);

// get a list of all commands
$result = $palava->call(COMMANDS, array('packages' => $config['packages']));
$commands = $result['commands'];


// TODO implement the logic
print_r($commands);


// at the end, disconnect
$palava->disconnect();
die();
?>