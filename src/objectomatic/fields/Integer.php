<?php

namespace objectomatic\fields;

class Integer extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

