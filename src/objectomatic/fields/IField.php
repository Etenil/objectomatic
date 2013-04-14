<?php

namespace objectomatic\fields;

interface IField
{
    /**
     * Sets the field name.
     */
    function setName($name);

    /**
     * Gets the field name.
     */
    function getName();

    /**
     * Returns the field options as an array.
     */
    function getOptions();

    /**
     * Gets a single option for convenience.
     */
    function getOption($name);

    /**
     * Returns the field's current value.
     */
    function getVal();

    /**
     * Sets the field's value.
     */
    function setVal($value);
}

