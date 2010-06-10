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

class EagerPalavaSession extends AbstractPalavaSession {
    
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
     * Index used in iterator methods.
     * 
     * @var int
     */
    private $index = -1;
    
    /**
     * Holds the current element used in iterator methods.
     * 
     * @var array (0 = key, 1 = value)
     */
    private $current = NULL;
    
    public function EagerPalavaSession(Palava $palava = NULL, $namespace = NULL) {
        $this->palava = $this->checkNotNull($palava, 'Palava');
        $this->namespace = $namespace;
    }
    
    public function contains($key = NULL) {
        $this->checkNotNull($key, 'Key');
        $result = $this->palava->call('de.cosmocode.palava.ipc.session.Contains', array(
            'keys' => array($key),
            'namespace' => $this->namespace
        ));
        return $result['status'][$key];
    }
    
    public function entries() {
        $result = $this->palava->call('de.cosmocode.palava.ipc.session.Entries', array(
            'namespace' => $this->namespace
        ));
        return $result['entries'];
    }
    
    public function &get($key = NULL) {
        $this->checkNotNull($key, 'Key');
        $result = $this->palava->call('de.cosmocode.palava.ipc.session.Get', array(
            'keys' => array($key),
            'namespace' => $this->namespace
        ));
        return $result['entries'][$key];
    }
    
    public function keys() {
        $result = $this->palava->call('de.cosmocode.palava.ipc.session.Keys', array(
            'namespace' => $this->namespace
        ));
        return $result['keys'];
    }
    public function remove($key = NULL) {
        $this->checkNotNull($key, 'Key');
        $this->palava->call('de.cosmocode.palava.ipc.session.Remove', array(
            'keys' => array($key),
            'namespace' => $this->namespace
        ));
    }

    public function set($key = NULL, $value = NULL) {
        $this->checkNotNull($key, 'Key');
        $this->palava->call('de.cosmocode.palava.ipc.session.Set', array(
            'entries' => array($key => $value),
            'namespace' => $this->namespace
        ));
    }
    
    public function synchronize($mode = PalavaSession::PUSH) {
        // not supported in this implementation
        return;
    }
    
    private function getCurrent($index = NULL) {
        if ($this->current === NULL) $this->increment();
        return $index === NULL ? $this->current : $this->current[$index];
    }
    
    private function increment() {
        $this->index++;
        $entries = $this->palava->call('de.cosmocode.palava.ipc.session.Entries', array(
            'sort' => TRUE,
            'namespace' => $this->namespace
        ));
        $keys = array_keys($entries);
        $key = $this->index < count($keys) ? $keys[$this->index] : NULL;
        $this->current = $key === NULL ? NULL : array($key, $entries[$key]);
    }
    
    public function current() {
        return $this->getCurrent(1);
    }

    public function next() {
        $this->increment();
    }

    public function key() {
        return $this->getCurrent(0);
    }

    public function valid() {
        return $this->getCurrent() !== NULL;
    }

    public function rewind() {
        $this->index = -1;
        $this->current = NULL;
    }
    
}

?>