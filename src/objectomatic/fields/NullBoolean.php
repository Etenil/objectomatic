<?php

namespace objectomatic\fields;

class NullBoolean extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

