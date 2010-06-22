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

/**
 * Palava IPC PHP JSON Connector Client implementation.
 *
 * @author Tobias Sarnowski
 */
class Palava {

	// used to read response from the server
	public static $BUFFER_CHUNK_SIZE = 8192;

	// cookie name for the session id cookie
	public static $SESSION_KEY = 'psessid';
    public static $COOKIE_PATH = '/';

	// protocol constants
	private static $PROTOCOL_KEY = "palava2/1.0";

	private static $PKEY_PROTOCOL = "protocol";
	private static $PKEY_SESSION = "session";
	private static $PKEY_COMMAND = "command";
	private static $PKEY_ARGUMENTS = "arguments";
	private static $PKEY_META = "meta";
    private static $PKEY_RESULT = "result";
    private static $PKEY_EXCEPTION = "exception";

	// will be configured
	private $host;
	private $port;

    // session data
    private $sessionKey;
    private $cookie_path;
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

	public function __construct($host, $port, $session_id = null) {
		$this->host = $host;
		$this->port = $port;
		$this->sessionKey = Palava::$SESSION_KEY;
        $this->cookie_path = Palava::$COOKIE_PATH;

        if (is_null($session_id)) {
		    if (isset($_COOKIE[$this->sessionKey])) {
			    $this->sessionId = $_COOKIE[$this->sessionKey];
		    }
            if (isset($_GET[$this->sessionKey])) {
                $this->sessionId = $_GET[$this->sessionKey];
            }
            if (isset($_POST[$this->sessionKey])) {
                $this->sessionId = $_POST[$this->sessionKey];
            }
        } else {
            $this->sessionId = $session_id;
        }
	}

    public function getSessionKey() {
		return $this->sessionKey;
	}

	public function getSessionId() {
		return $this->sessionId;
	}

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
		            throw new PalavaSession("Unknown mode $mode");
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
	
	public function connect($timeout = null) {
		if (is_null($timeout)) {
			$timeout = ini_get("default_socket_timeout");
		}
		// connect!
		$this->socket = @fsockopen($this->host, $this->port, $errno, $errmsg, $timeout);
		if (!$this->socket) {
			throw new PalavaConnectionException("cannot connect to backend ".$this->host.":".$this->port." within $timeout seconds: [$errno] $errmsg");
		}
	}
	
	public function connectLazily($timeout = NULL) {
        $this->timeout = $timeout === NULL ? ini_get("default_socket_timeout") : $timeout;
	}

	public function call($command, $arguments = null) {
		// not yet connected?
		if ($this->socket === NULL) {
			if ($this->timeout === NULL) {
				throw new PalavaException('Neither connect() nor connectLazily() has been called ');
			}
			$this->connect($this->timeout);
		}
		
		// test input
		if (!is_string($command)) {
			throw new PalavaArgumentsException("invalid command name");
		}
		if (is_null($arguments) or empty($arguments)) {
            $arguments = new stdclass();
		} else if (!is_array($arguments) && !($arguments instanceof stdclass)) {
			throw new PalavaArgumentsException("arguments invalid; array expected");
		}

        // request uri
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
            $request_uri = 'https://';
        } else {
            $request_uri = 'http://';
        }
        $request_uri .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		// build request
		$request = array();
		$request[Palava::$PKEY_PROTOCOL] = Palava::$PROTOCOL_KEY;
		$request[Palava::$PKEY_SESSION] = $this->sessionId;
		$request[Palava::$PKEY_COMMAND] = $command;
		$request[Palava::$PKEY_META] = array(
            'identifier' => $_SERVER['REMOTE_ADDR'],
            'request_uri' => $request_uri
        );
		$request[Palava::$PKEY_ARGUMENTS] = $arguments;

		// send it
		$json = json_encode($request);

        $call_start = microtime(true);
		if (!@fwrite($this->socket, $json)) {
			throw new PalavaConnectionException("cannot send request");
		}
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
		while (!feof($this->socket)) {
            $buffer .= @fread($this->socket, self::$BUFFER_CHUNK_SIZE);
			while ($json_pointer < strlen($buffer)) {
				$current = substr($buffer, $json_pointer, 1);
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
        PalavaStatistics::logCall($command, microtime(true) - $call_start);
		if (!$json_completed) {
			throw new PalavaConnectionException("cannot read response: ".$buffer);
		}

		// parse the response
		$response = json_decode($buffer, true);
		if (!$response) {
			throw new PalavaParseException("cannot parse response");
		}

		// check the right protocol
		if ($response[Palava::$PKEY_PROTOCOL] != Palava::$PROTOCOL_KEY) {
			throw new PalavaParseException("wrong protocol");
		}

		// set new session id if available
		if ($this->sessionId != $response[Palava::$PKEY_SESSION]) {
			$this->sessionId = $response[Palava::$PKEY_SESSION];
			setcookie($this->sessionKey, $this->sessionId, time() + 10*356*24*60*60, $this->cookie_path); // should not expire in a relevant future
		}

		// return result
		if (isset($response[Palava::$PKEY_RESULT])) {
			return $response[Palava::$PKEY_RESULT];
		} else {
			throw new PalavaExecutionException($response[Palava::$PKEY_EXCEPTION]);
		}
	}

	public function disconnect() {
		// not connected? nothing to do!
		if ($this->socket === NULL) return;
		if ($this->session !== NULL) {
			$this->session->synchronize();
		}
		@fclose($this->socket);
	}
	
}

?>