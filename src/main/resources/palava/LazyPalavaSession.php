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
 * Lazy PalavaSession implementation which caches data locally 
 * and synchronizes them with the remote session on demand.
 * 
 * @author Willi Schoenborn
 */
class LazyPalavaSession extends AbstractPalavaSession {
    
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
    
    /**
     * Holds local data. May never be used in eager mode.
     * A null value means the initial pull from the remote session
     * was not necessary.
     * 
     * @var array
     */
    private $data = NULL;
    
    private $removals = array();
    
    public function LazyPalavaSession(Palava $palava = NULL, $namespace = NULL) {
        $this->palava = $this->checkNotNull($palava, 'Palava');
        $this->namespace = $namespace;
    }
    
    private function preload() {
        if ($this->data === NULL) {
            $this->data = array();
            $this->synchronize(PalavaSession::PULL);
        } else {
            // already initialized, nothing to do
        }
    }
    
    public function contains($key = NULL) {
        $this->checkNotNull($key, 'Key');
        $this->preload();
        return array_key_exists($key, $this->data);
    }
    
    public function entries() {
        $this->preload();
        return $this->data;
    }
    
    public function &get($key = NULL) {
        $this->checkNotNull($key, 'Key');
        $this->preload();
        return $this->contains($key) ? $this->data[$key] : NULL;
    }
    
    public function keys() {
        $this->preload();
        return array_keys($this->data);
    }
    
    public function remove($key = NULL) {
        $this->checkNotNull($key, 'Key');
        $this->preload();
        unset($this->data[$key]);
        $this->removals[] = $key;
    }

    public function set($key = NULL, $value = NULL) {
        $this->checkNotNull($key, 'Key');
        $this->preload();
        $this->data[$key] = $value;
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
        
        // not yet initialized? no need to push!
        if ($this->data === NULL) return;
        
        // initialized, but empty? no need to push!
        if (count($this->data) == 0) return;
        
        // this will overwrite remote keys with local ones (remotely)
        $this->palava->call('de.cosmocode.palava.ipc.session.Set', array(
            'entries' => $this->data,
            'namespace' => $this->namespace
        ));
    }
    
    private function pull() {
        if ($this->data === NULL) $this->data = array();
        
        // this will overwrite local keys with remote ones (locally)
        $result = $this->palava->call('de.cosmocode.palava.ipc.session.Entries', array(
            'namespace' => $this->namespace
        ));
        
        $this->data = array_merge(
            $this->data, 
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
        return current($this->data);
    }

    public function next() {
        next($this->data);
    }

    public function key() {
        return key($this->data);
    }

    public function valid() {
        // null keys are not allowed, so this *should* be safe
        return $this->key() !== NULL;
    }

    public function rewind() {
        reset($this->data);
    }  
    
}

?>