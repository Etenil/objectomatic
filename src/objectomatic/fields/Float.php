<?php

namespace objectomatic\fields;

class Float extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

