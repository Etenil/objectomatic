<?php

/* I am a wrapper for Assegai. Use me if you install as a module. */

require('src/load.php');

/**
 * DAO utility.
 */
class Module_DAO extends \assegai\Module
{
    protected $driver;

    public static function instanciate()
    {
        return true;
    }

    function _init($options)
    {
        $this->connection = new \objectomatic\drivers\MySQLPDO($options);
    }

    function __call($name, array $args) {
        if(!method_exists($name, $this)) {
            return call_user_func_array(array($this->driver, $name), $args);
        }
    }
}
