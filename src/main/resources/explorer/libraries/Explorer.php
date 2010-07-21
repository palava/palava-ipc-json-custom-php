<?php 

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

        // parse the data
        foreach ($result['commands'] as $data) {
            Package::parseCommand($data);
        }

        // to have a nicer tree, summarize packages with just one sub package
        Package::summarizePackages();
    }
    
    public function __destruct() {
        $this->palava->disconnect();
    }
}