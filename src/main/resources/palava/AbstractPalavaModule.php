<?php

/**
 * Implements the preCall and postCall hooks with no actions.
 *
 */
abstract class AbstractPalavaModule implements PalavaModule {

    /**
     * @var Palava
     */
    private $palava;

    public function initialize(&$palava) {
        $this->palava = $palava;
    }

    /**
     * @return Palava the palava instance
     */
    protected function getPalava() {
        return $this->palava;
    }

    /**
     * @param  $key string the config key
     * @param bool $default default value if configuration not set
     * @return mixed the configuration value
     */
    protected function get($key, $default = false) {
        return $this->palava->get($key, $default);
    }

    /**
     * @param  $key string the config key
     * @param  $value string the config value
     * @return void
     */
    protected function set($key, $value) {
        $this->palava->set($key, $value);
    }


    public function preCall(&$call) {
        // nothing to do by default
        return null;
    }

    public function postCall(&$call, &$result) {
        // nothing to do by default
    }

}
