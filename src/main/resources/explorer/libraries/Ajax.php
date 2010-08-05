<?php
 
class Ajax {

    public static function runCommand($command, $parameters) {
        try {
            return Explorer::runCommand($command, $parameters);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
}
