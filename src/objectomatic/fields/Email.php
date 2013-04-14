<?php

namespace objectomatic\fields;

class Email extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

