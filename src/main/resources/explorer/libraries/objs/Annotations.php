<?php

/**
 * @author Tobias Sarnowski
 */
class Annotations {

    /**
     * @var string
     */
    private $name;


    function __construct($data) {
        $this->name = $data['name'];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}