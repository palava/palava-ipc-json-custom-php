<?php

// Example bootstrap

require('../palava/Palava.php');

$config['host'] = 'tcs.cosmo';
$config['port'] = 2001;
$config['packages'] = array('de.cosmocode', 'se.turistcentersyd');

require('explorer.inc.php');

?>