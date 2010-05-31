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

require_once 'PalavaSession.php';

/**
 * Abstract PalavaSession implementation which already fully implements
 * ArrayAccess, Countable and all magic methods.
 * 
 * @see ArrayAccess
 * @see Countable
 * @since 1.0
 * @author Willi Schoenborn
 */
abstract class AbstractPalavaSession implements PalavaSession {

    public function useGlobally() {
        $_SESSION = $this;
    }
    
    public function clear() {
        foreach ($this->keys() as $key) {
            $this->remove($key);
        }
    }
    
    public function offsetExists($offset) {
        $this->checkNotNull($offset, 'Offset');
        return $this->contains($offset);
    }

    public function offsetGet($offset) {
        $this->checkNotNull($offset, 'Offset');
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->checkNotNull($offset, 'Offset');
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->checkNotNull($offset, 'Offset');
        $this->remove($offset);
    }

    public function count() {
        return count($this->entries());
    }
    
    public function __get($key) {
        return $this->get($key);
    }
    
    public function __isset($key) {
        return $this->contains($key);
    }
    
    public function __set($key, $value) {
        $this->set($key, $value);
    }
    
    public function __unset($key) {
        $this->remove($key, $value);
    }
    
    protected function checkNotNull($ref, $message = '') {
        if ($ref === NULL) {
            throw new PalavaException("NullPointer $message");
        } else {
            return $ref;
        }
    }
    
}

?>