<?php

namespace objectomatic\fields;

class DateTime extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

