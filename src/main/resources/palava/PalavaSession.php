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

/**
 * A custom session implementation which allows accessing
 * the corresponding remote session with a user-friendly api.
 * This session implements ArrayAccess, Iterator and Countable 
 * which allows using an instance of this class as a substitute for $_SESSION.
 * 
 * @see ArrayAccess
 * @see Iterator
 * @see Countable
 * @since 1.0
 * @author Willi Schoenborn
 * @author Tobias Sarnowski
 */
interface PalavaSession extends ArrayAccess, Iterator, Countable {

    /**
     * Data is held locally and may be synchronized with the remote
     * session manually. Changes in the remote session won't be
     * visible until both sessions will be synchronized.
     */
    const LAZY = 'lazy';
    
    /**
     * No data will be held locally. Every data change
     * will be propagated to the remote session and all changes
     * to the remote session will be visible instantly.
     */
    const EAGER = 'eager';

    /**
     * The session data will be fetched from the backend at
     * start and be pushed back to the backend at the end.
     * Changes in the remote session won't be visible until
     * both sessions will be synchronized.
     */
    const NATIVE = 'native';
    
    /**
     * Pushes local changes to remote.
     */
    const PUSH = 'push';
    
    /**
     * Pulls remote changes to local.
     */
    const PULL = 'pull';
    
    /**
     * Fully synchronizes local and remote. In case of duplicate keys does
     * local have higher priority than remote and will overwrite.
     */
    const FULL_LOCAL = 'full-local';
    
    /**
     * Fully synchronizes local and remote. In case of duplicate keys does
     * remote have higher priority than local and will overwrite.
     */
    const FULL_REMOTE = 'full-remote';
    
    /**
     * Uses this session as the global $_SESSION.
     */
    public function useGlobally();
    
    /**
     * Checks whether this session contains the specified key.
     * 
     * @param $key the key to be tested
     * @return true if this session contains the specified key, false otherwise
     * @throws PalavaException if key is null
     */
    public function contains($key = NULL);
    
    /**
     * Provides all entries of this session.
     * 
     * @return an array containing all entries
     */
    public function entries();
    
    /**
     * Provides the value for the specified key currently
     * stored in this session.
     * 
     * @param $key the key
     * @return the found value or null if no mapping was present
     *         for the specified key
     * @throws PalavaException if key is null
     */
    public function &get($key = NULL);
    
    /**
     * Provides all keys currently present in this session.
     * 
     * @return an array containing all keys
     */
    public function keys();
    
    /**
     * Removes the specified key and the associated value
     * from this session.
     * 
     * @param $key the key of the entry to be removed
     * @throws PalavaException if key is null
     */
    public function remove($key = NULL);
    
    /**
     * Sets the key-value mapping to the specified parameters.
     * 
     * @param $key the key of the entry to be added/replaced
     * @param $value the new value
     * @throws PalavaException if key is null
     */
    public function set($key = NULL, $value = NULL);
    
    /**
     * Synchronizes this (local) session with the remote one.
     * Please refer to the predefined constants for
     * a detailed explanation of the different modes being supported.
     * 
     * @see PalavaSession::PUSH
     * @see PalavaSession::PULL
     * @see PalavaSession::FULL_LOCAL
     * @see PalavaSession::FULL_REMOTE
     * @param $mode the synchronization mode which specifies
     *        how synchronization should be performed
     * @throws PalavaExeption mode is null or mode is unsupported
     */
    public function synchronize($mode = PalavaSession::PUSH);
    
}

?>