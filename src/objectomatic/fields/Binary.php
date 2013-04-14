<?php

namespace objectomatic\fields;

class Binary extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

