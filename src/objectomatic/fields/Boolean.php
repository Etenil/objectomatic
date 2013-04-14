<?php

namespace objectomatic\fields;

class Boolean extends FieldBase implements IField
{
    protected function validate($value) {
        return is_bool($value);
    }
}

