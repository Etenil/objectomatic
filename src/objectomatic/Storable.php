<?php

namespace objectomatic;

/**
 * An object that can be stored in the database.
 */
class Storable
{
    protected $_id;
    
    final public function __construct(array $values = null)
    {
        $this->_id = new fields\PositiveInteger(array(
                        'primary_key' => true,
                        'increment' => true,
                    ));
        $this->_type_init();
        if($values) {
            $this->populate($values);
        }
    }

    /**
     * Populates the object from an associative array.
     */
    function populate(array $values)
    {
        foreach($values as $name => $value) {
            $setter = 'set' . $name;
            if(method_exists($this, 'set' . $name)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * Gets the object name. Defaults to the current class name.
     * Override if you want to name your object differently.
     * This can be useful if you use namespaces or just a
     * naming convention that looks ugly in the database.
     */
    public function getTypeName() {
        return get_class($this);
    }

    /**
     * Returns the object's signature.
     */
    final public function signature() {
        $signature = array();

        // We always put the id first. It's clearer that way.
        $this->_id->setName('_id');
        $signature[] = $this->_id;
        
        foreach($this as $pname => $pval) {
            if($pname == '_id') continue;
            if(is_object($pval) && $pval instanceof \objectomatic\fields\IField) {
                $pval->setName($pname);
                $signature[] = $pval;
            }
        }

        return $signature;
    }

    /**
     * Pretty-prints the object. Useful for debugging.
     */
    final public function prettyPrint() {
        echo "Storable " . get_class($this) . " {" . PHP_EOL;

        $format = function($val) {
            if(!is_numeric($val) && $val != 'false' &&
                $val != 'true' && $val != 'null') {
                return "\"$val\"";
            }
            else {
                return $val;
            }
        };

        printf("\tid: %s,\n", $format($this->_id->getVal()));
        foreach($this as $pname => $pval) {
            if($pname == '_id') continue;
            if(is_object($pval) && $pval instanceof \objectomatic\fields\IField) {
                printf("\t%s: %s,\n", $pname, $format($pval->getVal()));
            }
        }
        echo "}" . PHP_EOL;
    }

    function getId() {
        return $this->_id->getVal();
    }

    function setId($val) {
        $this->_id->setVal($val);
        return $this;
    }
}
