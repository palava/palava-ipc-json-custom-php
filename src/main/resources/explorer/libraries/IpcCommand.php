<?php

/**
 * Represents an IpcCommand.
 *
 * @author Tobias Sarnowski
 */
class IpcCommand {

    /**
     * the command's simple name
     * @var string
     */
    private $name;

    /**
     * the command's package
     * @var Package
     */
    private $package;


    /**
     * @param  $data array the unparsed data from Commands
     * @return void
     */
    function __construct($data, $package = null) {
        $this->package = $package;

        $parts = explode('.', $data['class']);
        $this->name = $parts[count($parts) - 1];

        // TODO parse data and fill in private variables
    }

    /**
     * @return string the command's simple name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string the command's full name
     */
    public function getFullName() {
        if (!is_null($this->package)) {
            return $this->package->getFullName().'.'.$this->getName();
        } else {
            return $this->getName();
        }
    }

    /**
     * @return Package
     */
    public function getPackage() {
        return $this->package;
    }


}