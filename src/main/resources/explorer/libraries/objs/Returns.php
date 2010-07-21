<?php

/**
 * @author Tobias Sarnowski
 */
class Returns {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;


    function __construct($data) {
        $this->name = $data['name'];
        $this->description = $data['description'];
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
}