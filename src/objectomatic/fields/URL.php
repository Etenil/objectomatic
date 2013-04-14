<?php

namespace objectomatic\fields;

class URL extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

