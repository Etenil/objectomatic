<?php

namespace objectomatic\fields;

class PositiveInteger extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

