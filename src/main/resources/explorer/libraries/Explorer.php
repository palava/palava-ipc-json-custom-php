<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer."); 

class Explorer {
    private $config = array(
        'host' => 'localhost',
        'port' => 2001,
        'packages' => array('de.cosmocode')
    );
    
    private $palava = null;
    
    public function __construct($config = array()) {
        $this->config = array_merge($this->config, $config);

        // connect to the backend
        $this->palava = new Palava($this->config['host'], $this->config['port']);
        $this->palava->connect();
    
        // get a list of all commands
        $result = $this->palava->call(
            'de.cosmocode.palava.ipc.json.custom.php.explorer.Commands',
            array(
                'packages' => $this->config['packages']
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

    private static function compareCommands($c1, $c2) {
        return strcmp($c1['class'], $c2['class']);
    }
    
    public function __destruct() {
        $this->palava->disconnect();
    }
}