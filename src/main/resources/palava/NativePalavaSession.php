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

require_once 'AbstractPalavaSession.php';

/**
 * Native PalavaSession implementation which uses $_SESSION
 * to store session data. Synchronizes on start end stop only.
 * 
 * @author Tobias Sarnowski
 */
class NativePalavaSession extends AbstractPalavaSession {
    
    /**
     * Palava instance used for access the remote session.
     * 
     * @var Palava
     */
    private $palava;
    
    /**
     * Namespace being used.
     * 
     * @var string
     */
    private $namespace = NULL;

    
    private $removals = array();
    
    public function NativePalavaSession(Palava $palava = NULL, $namespace = NULL) {
        $this->palava = $this->checkNotNull($palava, 'Palava');
        $this->namespace = $namespace;

        $this->synchronize(PalavaSession::PULL);
    }

    public function useGlobally() {
        // we already use the $_SESSION
    }
    
    public function contains($key = NULL) {
        $this->checkNotNull($key, 'Key');
        return array_key_exists($key, $_SESSION);
    }
    
    public function entries() {
        return $_SESSION;
    }
    
    public function &get($key = NULL) {
        $this->checkNotNull($key, 'Key');
        return $this->contains($key) ? $_SESSION[$key] : NULL;
    }
    
    public function keys() {
        return array_keys($_SESSION);
    }
    
    public function remove($key = NULL) {
        $this->checkNotNull($key, 'Key');
        unset($_SESSION[$key]);
        $this->removals[] = $key;
    }

    public function set($key = NULL, $value = NULL) {
        $this->checkNotNull($key, 'Key');
        $_SESSION[$key] = $value;
        $index = array_search($key, $this->removals);
        if ($index) {
            unset($this->removals[$index]);
        }
    }

    public function synchronize($mode = PalavaSession::PUSH) {
        $this->checkNotNull($mode, 'Mode');
        switch ($mode) {
            case PalavaSession::PUSH: {
                $this->push();
                break;
            }
            case PalavaSession::PULL: {
                $this->pull();
                break;
            }
            case PalavaSession::FULL_LOCAL: {
                $this->fullLocal();
                break;
            }
            case PalavaSession::FULL_REMOTE: {
                $this->fullRemote();
                break;
            }
            default: {
                throw new PalavaException("mode must be one of 'push', 'pull', 'full-local' or 'full-remote' but was $mode");
            }
        }
    }
    
    private function push() {
        if (count($this->removals) > 0) {
            $this->palava->call('de.cosmocode.palava.ipc.session.Remove', array(
                'keys' => $this->removals,
                'namespace' => $this->namespace
            ));
            $this->removals = array();
        }
        
        // initialized, but empty? no need to push!
        if (count($_SESSION) == 0) return;
        
        // this will overwrite remote keys with local ones (remotely)
        $this->palava->call('de.cosmocode.palava.ipc.session.Set', array(
            'entries' => $_SESSION,
            'namespace' => $this->namespace
        ));
    }
    
    private function pull() {
        // this will overwrite local keys with remote ones (locally)
        $result = $this->palava->call('de.cosmocode.palava.ipc.session.Entries', array(
            'namespace' => $this->namespace
        ));

        if (!isset($_SESSION) || empty($_SESSION)) {
            $_SESSION = array();
        }
        $_SESSION = array_merge(
            $_SESSION,
            $result['entries']
        );
    }
    
    private function fullLocal() {
        $this->push();
        $this->pull();
    }
    
    private function fullRemote() {
        $this->pull();
        $this->push();
    } 
    
    public function current() {
        return current($_SESSION);
    }

    public function next() {
        next($_SESSION);
    }

    public function key() {
        return key($_SESSION);
    }

    public function valid() {
        // null keys are not allowed, so this *should* be safe
        return $this->key() !== NULL;
    }

    public function rewind() {
        reset($_SESSION);
    }  
    
}

?>