<?php

namespace objectomatic\fields;

class FilePath extends FieldBase implements IField
{
    protected function validate($value) {
        return true;
    }
}

