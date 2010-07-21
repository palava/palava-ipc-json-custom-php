<?php 

class Explorer {
    private $config = array(
        'host' => 'localhost',
        'port' => 2001,
        'packages' => array('de.cosmocode')
    );
    
    private $palava = null;
    private $commands;
    private $tree;
    
    public function __construct($config = array()) {
        $this->config = array_merge($this->config, $config);
        
        $this->palava = new Palava($this->config['host'], $this->config['port']);
        $this->palava->connect();
    
        // get a list of all commands
        $result = $this->palava->call('de.cosmocode.palava.ipc.json.custom.php.explorer.Commands', array('packages' => $this->config['packages']));
        $this->commands = $result['commands'];
        
        $this->tree = $this->make_tree($this->commands);
    }
    
    public function make_tree() {
        $tree = array();
        
        foreach ($commands as $command) {
            $package = explode($command['class'],'.');
            
        }
        
        return $tree;
    }
    
    public function getCommands() {
        return $this->commands;
    }
    
    public function __destruct() {
        $this->palava->disconnect();
    }
}