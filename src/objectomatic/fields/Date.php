<?php

namespace objectomatic\fields;

class Date extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

