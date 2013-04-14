<?php

namespace objectomatic\drivers;

use \objectomatic\Storable;

/**
 * Driver interface for DAO.
 */
interface IDriver
{
    // Simple CRUD.
    function load(Storable $object, $id = null);
    function loadMulti(Storable $object, $where = null);
    function create(Storable $object);
    function insert(Storable $object);
    function update(Storable $object);
    function updateMulti(Storable $replacement, $where = null);
    function delete(Storable $object);
    function deleteMulti(Storable $object, $where = null);
    function drop(Storable $object);
}
