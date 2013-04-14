<?php

namespace objectomatic\fields;

class ForeignKey extends FieldBase implements IField
{
    protected $_foreign_type;
    
    function __construct(\objectomatic\Storable $other, array $options = null) {
        $this->setForeignType($other);
        if($options) {
            $this->setOptions($options);
        }
    }
    
    protected function validate($value) {
        $type = $this->getForeignType();
        return $value instanceof $type;
    }

    protected function setForeignType(\objectomatic\Storable $object) {
        $this->_foreign_object = $object;
        return $this;
    }

    function getForeignType() {
        return $this->_foreign_object;
    }
}

