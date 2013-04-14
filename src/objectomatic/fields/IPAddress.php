<?php

namespace objectomatic\fields;

class IPAddress extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

