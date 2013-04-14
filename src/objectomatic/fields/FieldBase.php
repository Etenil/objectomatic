<?php

namespace objectomatic\fields;

abstract class FieldBase implements IField
{
    protected $_name;
    protected $_options;
    protected $_value;
    
    function __construct(array $options = null)
    {
        if($options) {
            $this->setOptions($options);
        }
    }

    function getName() {
        return $this->_name;
    }

    function setName($name) {
        $this->_name = trim($name, "_");
    }

    /**
     * Returns the field options as an array.
     */
    function getOptions()
    {
        if(!is_array($this->_options)) {
            return array();
        } else {
            return $this->_options;
        }
    }

    function getOption($name)
    {
        $options = $this->getOptions();
        if(isset($name, $options)) {
            return $options[$name];
        } else {
            return null;
        }
    }

    function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Returns the field's current value.
     */
    function getVal()
    {
        return $this->_value;
    }

    /**
     * Little convenience for the validations.
     */
    protected function isNull($value) {
        return (is_object($value) && $value instanceof NullFieldValue);
    }

    /**
     * Sets the field's value.
     */
    function setVal($value) {
        if(!$this->validate($value)) {
            
            throw new \objectomatic\errors\TypeError(
                "Couldn't set value of field type '".get_class($this)."' to '$value'"
            );
        } else {
            $this->_value = $value;
        }
    }

    /**
     * Validates the user input.
     */
    abstract protected function validate($value);
}

