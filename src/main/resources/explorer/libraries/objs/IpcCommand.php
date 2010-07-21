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
     * the command's description
     * @var string
     */
    private $description;

    /**
     * list of parameters
     * @var array
     */
    private $params;

    /**
     * list of results
     * @var array
     */
    private $returns;

    /**
     * list of exceptions
     * @var array
     */
    private $throws;

    /**
     * list of additional annotations
     * @var array
     */
    private $annotations;


    /**
     * @param  $data array the unparsed data from Commands
     * @return void
     */
    function __construct($data, $package = null) {
        $this->package = $package;

        $parts = explode('.', $data['class']);
        $this->name = $parts[count($parts) - 1];

        if (isset($data['description'])) {
            $this->description = $data['description'];
        } else {
            $this->description = null;
        }

        $this->params = array();
        foreach ($data['params'] as $param) {
            $this->params[] = new Params($param);
        }

        $this->returns = array();
        foreach ($data['returns'] as $returns) {
            $this->returns[] = new Returns($returns);
        }

        $this->throws = array();
        foreach ($data['throws'] as $throws) {
            $this->throws[] = new Throws($throws);
        }

        $this->annotations = array();
        foreach ($data['annotations'] as $annotations) {
            $this->annotations[] = new Annotations($annotations);
        }
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

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getReturns() {
        return $this->returns;
    }

    /**
     * @return array
     */
    public function getThrows() {
        return $this->throws;
    }

    /**
     * @return array
     */
    public function getAnnotations() {
        return $this->annotations;
    }

}