<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer.");
if (!defined('IS_AJAX') || IS_AJAX == false) die('No Ajax-Call detected');

switch ($_POST['ajax']) {
    case 'runCommand':
        $params = json_decode(str_replace('\"', '"', $_POST['parameters']), true);
        $result = Ajax::runCommand(COMMAND, $params);

        if (is_array($result))
            die(print_r($result, true));

        die($result);
    break;
}