<?php

namespace objectomatic\fields;

class Text extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

