<?php

/**
 * Classes in the modules/ subdirectory must extend this class.
 *
 */
interface PalavaModule {

    /**
     * @param $palava Palava the palava instance
     * @return void
     */
    public function initialize(&$palava);

    /**
     * Will be called before the call gets send.
     *
     * @abstract
     * @param  $call array the call which will be send
     * @return mixed null if the call should be send to the server or
     *                    the result which will be returned immediately.
     */
    public function preCall(&$call);

    /**
     * Will be called after the call got the response from the server.
     *
     * @abstract
     * @param  $call array the call which was sent
     * @param  $result array the result gotten from the server
     * @return void
     */
    public function postCall(&$call, &$result);

}
