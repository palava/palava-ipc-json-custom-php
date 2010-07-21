<?php

/**
 * Represents a single package in the class hierarchy
 *
 * @author Tobias Sarnowski
 */
class Package {

    /**
     * list of root packages or commands
     * @var array
     */
    private static $roots = array();


    /**
     * the package's simple name
     * @var string
     */
    private $name;

    /**
     * the package's parent package or null if root package
     * @var string
     */
    private $parent;

    /**
     * sub packages
     * @var array
     */
    private $packages;

    /**
     * commands in this package
     * @var array
     */
    private $commands;


    /**
     * @param  $name string the package's simple name
     * @param  $parent Package an optional parent of not root package
     * @return void
     */
    protected function __construct($name, $parent = null) {
        $this->name = $name;
        $this->parent = $parent;
        $this->packages = array();
        $this->commands = array();
    }

    /**
     * @return string the package's simple name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string the package's full name
     */
    public function getFullName() {
        if (!is_null($this->parent)) {
            return $this->parent->getFullName().'.'.$this->getName();
        } else {
            return $this->getName();
        }
    }

    /**
     * @return string the package's parent package
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @return array all sub packages
     */
    public function getPackages() {
        return $this->packages;
    }

    /**
     * @return array all commands
     */
    public function getCommands() {
        return $this->commands;
    }


    /**
     * @static
     * @return array list of root packages or commands
     */
    public static function getRoots() {
        return self::$roots;
    }

    /**
     * @static
     * @param  $data array parses data given by the Command command
     * @return void
     */
    public static function parseCommand($data) {
        // parse the package name
        $parts = explode('.', $data['class']);
        unset($parts[count($parts) - 1]);

        // create the package
        $package = self::createPackage($parts);

        // form the command
        $command = new IpcCommand($data, $package);

        // and add it
        if (!is_null($package)) {
            $package->commands[] = $command;
        } else {
            self::$roots[] = $command;
        }
    }

    /**
     * @static
     * @param  $parts array list of package parts
     * @return Package the created package or null if no package name given
     */
    private static function createPackage($parts) {
        if (empty($parts)) {
            return null;
        }

        foreach (self::$roots as $root) {
            if ($root->getName() == $parts[0]) {
                array_shift($parts);
                return self::_createPackage($root, $parts);
            }
        }

        $package = new Package($parts[0]);
        self::$roots[] = $package;

        array_shift($parts);
        return self::_createPackage($package, $parts);
    }

    /**
     * @static
     * @param  $package Package
     * @param  $parts array
     * @return Package
     */
    private static function _createPackage($package, $parts) {
        if (empty($parts)) {
            return $package;
        }

        foreach ($package->getPackages() as $pkg) {
            if ($pkg->getName() == $parts[0]) {
                array_shift($parts);
                return self::_createPackage($pkg, $parts);
            }
        }

        $pkg = new Package($parts[0], $package);
        $package->packages[] = $pkg;

        array_shift($parts);
        return self::_createPackage($pkg, $parts);
    }


    public static function summarizePackages() {
        foreach (Package::$roots as $key => $package) {
            if (!($package instanceof Package)) {
                continue;
            }

            Package::$roots[$key] = self::summarizePackage($package);
        }
    }

    private static function summarizePackage($package) {
        if (count($package->getCommands()) == 0 && count($package->getPackages()) == 1) {
            // we can summarize this package with the only subpackage
            $pkg = self::summarizePackage($package->packages[0]);
            $pkg->name = $package->getName().'.'.$pkg->getName();
            $pkg->parent = $package->getParent();
            $package = $pkg;
        }

        // do it for all children
        foreach ($package->packages as $key => $pkg) {
            $package->packages[$key] = self::summarizePackage($pkg);
        }
        return $package;
    }
}