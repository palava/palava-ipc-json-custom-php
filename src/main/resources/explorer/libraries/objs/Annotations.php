<?php

class Annotations {

    /**
     * @var string
     */
    private $name;


    function __construct($data) {
        $this->name = $data['name'];
    }

    /**
     * @return ?#M#CAnnotations.name
     */
    public function getName() {
        return $this->name();
    }
}