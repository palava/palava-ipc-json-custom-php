<?php

class PalavaStatistics {

    private static $startTime = 0;

    private static $logs = array();


    public static function start() {
        self::$startTime = microtime(true);
    }


    public static function logCall($command, $microtime) {
        self::$logs[] = array('command' => $command, 'microtime' => $microtime);
    }


    public static function get() {
        return array('startTime' => self::$startTime, 'logs' => self::$logs);
    }

    public static function simple() {
        $stats = self::get();

        $javaTime = 0;

        foreach ($stats['logs'] as $log) {
            $javaTime += $log['microtime'];
        }

        $allTime = microtime(true) - $stats['startTime'];
        $phpTime = $allTime - $javaTime;

        unset($stats['startTime']);
        $stats['all'] = $allTime;
        $stats['java'] = $javaTime;
        $stats['php'] = $phpTime;

        $stats['java_percent'] = (int)($javaTime * 100 / $allTime);
        $stats['php_percent'] = (int)($phpTime * 100 / $allTime);

        return $stats;
    }

}

// start logging NOW
PalavaStatistics::start();