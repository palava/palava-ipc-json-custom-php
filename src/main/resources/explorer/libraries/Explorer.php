<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer."); 

class Explorer {
    private static $config = array(
        'host' => 'localhost',
        'port' => 2001,
        'packages' => array('de.cosmocode')
    );

    /**
     * @var Palava
     */
    private static $palava = null;
    
    public static function init($config = array()) {
        self::$config = array_merge(self::$config, $config);

        // connect to the backend
        self::$palava = new Palava(self::$config['host'], self::$config['port']);

        // set palavaConf
        if (isset($config['palavaConf'])) {
            foreach ($config['palavaConf'] as $key => $value) {
                self::$palava->set($key, $value);
            }
        }

        self::$palava->connectLazily();
    
        // get a list of all commands
        $result = self::$palava->call(
            'de.cosmocode.palava.ipc.json.custom.php.explorer.Commands',
            array(
                'packages' => self::$config['packages']
            )
        );

        // sort the classes
        uasort($result['commands'], array('Explorer', 'compareCommands'));

        // parse the data
        foreach ($result['commands'] as $data) {
            Package::parseCommand($data);
        }

        // to have a nicer tree, summarize packages with just one sub package
        Package::summarizePackages();
    }

    public static function runCommand($command, $params = array()) {
        if (!is_array($params)) $params = array();
        return self::$palava->call($command, $params);
    }

    private static function compareCommands($c1, $c2) {
        return strcmp($c1['class'], $c2['class']);
    }
    
    public static function disconnect() {
        self::$palava->disconnect();
    }
}