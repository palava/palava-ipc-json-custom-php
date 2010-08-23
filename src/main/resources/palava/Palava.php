<?php
/*
 * Copyright 2010 CosmoCode GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require('PalavaException.php');
require('PalavaArgumentsException.php');
require('PalavaConnectionException.php');
require('PalavaExecutionException.php');
require('PalavaParseException.php');
require('PalavaSession.php');
require('LazyPalavaSession.php');
require('EagerPalavaSession.php');
require('NativePalavaSession.php');
require('PalavaStatistics.php');
require('PalavaModule.php');
require('AbstractPalavaModule.php');

/**
 * Palava IPC PHP JSON Connector Client implementation.
 *
 * @author Tobias Sarnowski
 */
class Palava {

	// protocol constants
	const PROTOCOL_KEY = "palava2/1.1";

	const PKEY_PROTOCOL = "protocol";
	const PKEY_SESSION = "session";
	const PKEY_COMMAND = "command";
	const PKEY_ARGUMENTS = "arguments";
	const PKEY_META = "meta";
    const PKEY_RESULT = "result";
    const PKEY_EXCEPTION = "exception";

    // core configuration keys
    const CONFIG_HOST = 'palava.host';
    const CONFIG_PORT = 'palava.port';
    const CONFIG_BUFFERSIZE = 'palava.bufferSize';
    const CONFIG_TIMEOUT = 'palava.timeout';
    const CONFIG_SESSIONKEY = 'palava.sessionKey';
    const CONFIG_COOKIEPATH = 'palava.cookiePath';
    const CONFIG_MODULEDIRECTORY = 'palava.moduleDirectory';

    const DEFAULT_BUFFERSIZE = 8192;
    const DEFAULT_SESSIONKEY = 'psessid';
    const DEFAULT_COOKIEPATH = '/';
    const DEFAULT_MODULEDIRECTORY = 'modules';

    private $meta_server_keys = array(
        'REQUEST_METHOD',
        'REQUEST_URI',
        // remote address will be added by getUserIp()
        'HTTP_HOST',
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_CHARSET',
        // https? will be added by isHttpsOn()
        'HTTP_REFERER',
    );


    // configuration options
    private $config = array();

    // session data
    private $sessionId;

	// the socket of our connection
	private $socket = NULL;
	private $timeout = NULL;

	/**
	 * The current palava session or null if the session class was not used.
	 * 
	 * @var PalavaSession
	 */
	private $session = NULL;

    /**
     * All loaded modules.
     *
     * @var array
     */
    private $modules = array();


	public function __construct($host, $port, $session_id = NULL, $config = array()) {
        $this->config = $config;

        $this->set(Palava::CONFIG_HOST, $host);
        $this->set(Palava::CONFIG_PORT, $port);

        // get the session ID
        if ($session_id === NULL) {
            $skey = $this->getSessionKey();
		    if (array_key_exists($skey, $_COOKIE)) {
			    $this->sessionId = $_COOKIE[$skey];
		    }
		    // GET takes precedence over COOKIE
            if (array_key_exists($skey, $_GET)) {
                $this->sessionId = $_GET[$skey];
            }
            // POST takes precedence over GET
            if (array_key_exists($skey, $_POST)) {
                $this->sessionId = $_POST[$skey];
            }
        } else {
            $this->sessionId = $session_id;
        }

        // load modules
        $module_dir = dirname(__FILE__).'/'.$this->get(Palava::CONFIG_MODULEDIRECTORY, Palava::DEFAULT_MODULEDIRECTORY);
        if (file_exists($module_dir)) {
            $dh = dir($module_dir);
            $ext = '.php';
            while (false !== ($entry = $dh->read())) {
                if (($offset = strrpos($entry, $ext)) === false) {
                    // .php is not even in the string
                    continue;
                }
                if ($offset != (strlen($entry) - strlen($ext))) {
                    // .php is not at the end of the string
                    continue;
                }

                $module_name = substr($entry, 0, $offset);
                include_once("$module_dir/$module_name$ext");

                $module = new $module_name();
                $module->initialize($this);

                $this->modules[$module_name] = $module;
            }
            $dh->close();
        }
	}

    /**
     * Retrieves a configuration value.
     *
     * @param $key string the configuration key
     * @param $default mixed default value if configuration is not set
     * @return mixed
     */
    public function get($key, $default = false) {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        } else {
            return $default;
        }
    }

    /**
     * @return array all configurations
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Sets a configuration value
     *
     * @param $key string the configuration key
     * @param $value mixed the configuration value
     * @return void
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
    }

    /**
     * @return PalavaModule a loaded module.
     */
    public function module($module_name) {
        return $this->modules[$module_name];
    }

    /**
     * @return string the used session key
     */
    public function getSessionKey() {
		return $this->get(Palava::CONFIG_SESSIONKEY, Palava::DEFAULT_SESSIONKEY);
	}

    /**
     * @return string the used session id
     */
	public function getSessionId() {
		return $this->sessionId;
	}

    /**
     * Sets a new session ID
     *
     * @param $session_id string the new session id
     * @return void
     */
    public function setSessionId($session_id) {
        $this->sessionId = $session_id;
    }

	/**
	 * Provides an instance of PalavaSession to easily operate with session
	 * data. Repeated calls to this function will return the session
	 * instance create at the first call. If lazy mode was choosen,
	 * changes to the session will be pushed back to the remote session
	 * right before this palava instance disconnects. 
	 * 
	 * @param $mode the session operation mode, please refer to PalavaSession
	 *        for more details, defaults to lazy
	 * @param $namespace the session namespace being used
	 */
	public function getSession($mode = PalavaSession::LAZY, $namespace = NULL) {
		if ($this->session === NULL) {
		    switch ($mode) {
		        case PalavaSession::LAZY: {
		            $this->session = new LazyPalavaSession($this, $namespace);
		            break;
		        }
		        case PalavaSession::EAGER: {
		            $this->session = new EagerPalavaSession($this, $namespace);
		            break;
		        }
                case PalavaSession::NATIVE: {
		            $this->session = new NativePalavaSession($this, $namespace);
		            break;
		        }
		        default: {
		            throw new PalavaException("Unknown mode $mode");
		        }
		    }
		}
		return $this->session;
	}
	
	/**
	 * Uses the session returned by $this->getSession(..) and uses it at
	 * $_SESSION by calling $this->session->useGlobally().
	 * 
     * @param $mode the session operation mode, please refer to PalavaSession
     *        for more details, defaults to lazy
	 */
	public function useSessionGlobally($mode = PalavaSession::LAZY, $namespace = NULL) {
		$this->getSession($mode, $namespace)->useGlobally();
	}
	
	public function connect($timeout = NULL) {
		$timeout = $timeout === NULL ? $this->get(Palava::CONFIG_TIMEOUT, ini_get("default_socket_timeout")) : $timeout;

		// connect!
        $host = $this->get(Palava::CONFIG_HOST);
        $port = $this->get(Palava::CONFIG_PORT);
		$this->socket = @fsockopen($host, $port, $errno, $errmsg, $timeout);
		if (!$this->socket) {
			throw new PalavaConnectionException("cannot connect to backend $host:$port within $timeout seconds: [$errno] $errmsg");
		}
	}
	
	public function connectLazily($timeout = NULL) {
        $this->timeout = $timeout === NULL ? $this->get(Palava::CONFIG_TIMEOUT, ini_get("default_socket_timeout")) : $timeout;
	}

    private function getUserIp() {
        if (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function isHttpsOn() {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return true;
        } else {
            return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
        }
    }

    private function generateMetaInformations() {
        $meta = array();
        
        foreach ($this->meta_server_keys as $key) {
            if (!isset($_SERVER[$key])) {
                $meta[$key] = '';
            } else {
                $meta[$key] = $_SERVER[$key];
            }
        }

        $meta['REMOTE_ADDR'] = $this->getUserIp();
        $meta['HTTPS'] = $this->isHttpsOn();

        return $meta;
    }

	public function call($command, $arguments = null) {
		// test input
		if (!is_string($command)) {
			throw new PalavaArgumentsException("invalid command name");
		}
		if (empty($arguments)) {
            $arguments = new stdclass();
		} else if (!is_array($arguments) && !($arguments instanceof stdclass)) {
			throw new PalavaArgumentsException("arguments invalid; array expected");
		}

        // request uri
        if (array_key_exists('HTTPS', $_SERVER) && strtolower($_SERVER['HTTPS']) == 'on') {
            $request_uri = 'https://';
        } else {
            $request_uri = 'http://';
        }
        $request_uri .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		// build request
		$request = array();
		$request[Palava::PKEY_PROTOCOL] = Palava::PROTOCOL_KEY;
		$request[Palava::PKEY_SESSION] = $this->sessionId;
		$request[Palava::PKEY_COMMAND] = $command;
		$request[Palava::PKEY_META] = $this->generateMetaInformations();
		$request[Palava::PKEY_ARGUMENTS] = $arguments;

        // module hook
        $result = null;
        foreach ($this->modules as $module) {
            $r = $module->preCall($request);

            if (is_null($r)) {
                $result = $r;
                break;
            }
        }

        if (is_null($result)) {

            // not yet connected?
            if ($this->socket === NULL) {
                if ($this->timeout === NULL) {
                    throw new PalavaException('Neither connect() nor connectLazily() has been called ');
                }
                $this->connect($this->timeout);
            }

            // send it
            $json = json_encode($request);
            $size_sent = strlen($json);
            $packets_sent = 0;

            $call_start = microtime(true);
            // TODO send until everything is done even if it requires multiple packets?
            if (!@fwrite($this->socket, $json)) {
                throw new PalavaConnectionException("cannot send request");
            }
            $packets_sent++;
            if (!@fflush($this->socket)) {
                throw new PalavaConnectionException("cannot flush request");
            }

            // read the response
            $buffer = '';
            $json_pointer = 0;
            $json_counter = 0;
            $json_in_string = false;
            $json_is_escaped = false;
            $json_completed = false;
            $packets_gotten = 0;
            $buffer_size = 0;
            $buffer_chunk_size = $this->get(Palava::CONFIG_BUFFERSIZE, Palava::DEFAULT_BUFFERSIZE);
            while (!feof($this->socket)) {
                $buffer .= @fread($this->socket, $buffer_chunk_size);
                $packets_gotten++;
                $buffer_size = strlen($buffer);
                while ($json_pointer < $buffer_size) {
                    $current = $buffer[$json_pointer];
                    if (!$json_in_string) {
                        if ($current == '"') {
                            $json_in_string = true;
                        } else if ($current == '{') {
                            $json_counter++;
                        } else if ($current == '}') {
                            $json_counter--;
                            if ($json_counter == 0) {
                                $json_completed = true;
                                break;
                            }
                        }
                    } else {
                        if ($current == '"' && !$json_is_escaped) {
                            $json_in_string = false;
                        } else if ($current == '\\' && !$json_is_escaped) {
                            $json_is_escaped = true;
                        } else if ($json_is_escaped) {
                            $json_is_escaped = false;
                        }
                    }
                    $json_pointer++;
                }
                if ($json_completed) {
                    break;
                }
            }
            PalavaStatistics::logCall($command, microtime(true) - $call_start, $size_sent, $packets_sent, $buffer_size, $packets_gotten);
            if (!$json_completed) {
                throw new PalavaConnectionException("cannot read response: ".$buffer);
            }

            // parse the response
            $response = json_decode($buffer, true);
            if (!$response) {
                throw new PalavaParseException("cannot parse response");
            }

            // check the right protocol
            if ($response[Palava::PKEY_PROTOCOL] != Palava::PROTOCOL_KEY) {
                throw new PalavaParseException("wrong protocol");
            }

            // module hook
            foreach ($this->modules as $module) {
                $result = $module->postCall($request, $response);
            }

        }

		// set new session id if available
		if ($this->sessionId != $response[Palava::PKEY_SESSION]) {
			$this->sessionId = $response[Palava::PKEY_SESSION];
            $cookie_path = $this->get(Palava::CONFIG_COOKIEPATH, Palava::DEFAULT_COOKIEPATH);
			setcookie($this->getSessionKey(), $this->getSessionId(), time() + 10*356*24*60*60, $cookie_path); // should not expire in a relevant future
		}

		// return result
		if (array_key_exists(Palava::PKEY_RESULT, $response)) {
			return $response[Palava::PKEY_RESULT];
		} else {
			throw new PalavaExecutionException($response[Palava::PKEY_EXCEPTION]);
		}
	}

	public function disconnect() {
		// not connected? nothing to do!
		if ($this->socket === NULL) return;
		if ($this->session !== NULL) {
			$this->session->synchronize();
            $this->session = NULL;
		}
		@fclose($this->socket);
        $this->socket = NULL;
	}
	
}

?>