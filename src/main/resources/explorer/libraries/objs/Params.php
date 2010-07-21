<?php

class Params {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var string
     */
    private $defaultValue;


    function __construct($data) {
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->type = $data['type'];
        $this->optional = $data['optional'];
        $this->defaultValue = $data['defaultValue'];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function getOptional() {
        return $this->optional;
    }

    /**
     * @return string
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

}