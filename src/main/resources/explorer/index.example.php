<?php

// Example bootstrap

require('../palava/Palava.php');

$config = array(
    'host' => 'localhost',
    'port' => 2001,
    'packages' => array('de.cosmocode', 'se.turistcentersyd')
);

require('explorer.inc.php');

?>