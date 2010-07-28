<?php

class PalavaStatistics {

    private static $startTime = 0;

    private static $logs = array();


    public static function start() {
        self::$startTime = microtime(true);
    }


    public static function logCall($command, $microtime, $bytesSent, $packetsSent, $bytesGotten, $packetsGotten) {
        self::$logs[] = array(
            'command' => $command,
            'microtime' => $microtime,
            'bytesSent' => $bytesSent,
            'packetsSent' => $packetsSent,
            'bytesGotten' => $bytesGotten,
            'packetsGotten' => $packetsGotten
        );
    }


    public static function get() {
        return array('startTime' => self::$startTime, 'logs' => self::$logs);
    }

    public static function simple() {
        $stats = self::get();

        $javaTime = 0;
        $bytesSent = 0;
        $packetsSent = 0;
        $bytesGotten = 0;
        $packetsGotten = 0;

        foreach ($stats['logs'] as $log) {
            $javaTime += $log['microtime'];
            $bytesSent += $log['bytesSent'];
            $packetsSent += $log['packetsSent'];
            $bytesGotten += $log['bytesGotten'];
            $packetsGotten += $log['packetsGotten'];
        }

        $allTime = microtime(true) - $stats['startTime'];
        $phpTime = $allTime - $javaTime;

        unset($stats['startTime']);
        $stats['all'] = $allTime;
        $stats['java'] = $javaTime;
        $stats['php'] = $phpTime;

        $stats['java_percent'] = (int)($javaTime * 100 / $allTime);
        $stats['php_percent'] = (int)($phpTime * 100 / $allTime);

        $stats['bytes_sent'] = $bytesSent;
        $stats['bytes_gotten'] = $bytesGotten;
        $stats['packets_sent'] = $packetsSent;
        $stats['packets_gotten'] = $packetsGotten;

        return $stats;
    }

}

// start logging NOW
PalavaStatistics::start();