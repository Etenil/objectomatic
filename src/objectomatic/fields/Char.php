<?php

namespace objectomatic\fields;

class Char extends FieldBase implements IField
{
    protected function validate($value) {
        return ($this->isNull($value)
            || strlen($value) <= $this->getOption('max_length'));
    }
}

