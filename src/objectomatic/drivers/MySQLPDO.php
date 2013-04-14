<?php

namespace objectomatic\drivers;

use \objectomatic\Storable;
use \objectomatic\fields\IField;

use \objectomatic\errors\TypeError;
use \objectomatic\errors\KeyError;
use \objectomatic\errors\DriverError;

class MySQLPDO implements IDriver
{
    protected $connection;
    protected $debug = false;

    /**
     * Constructor for mysqlpdo. takes an array as parameter as follows:
     *   host     => database host
     *   database => database name
     *   username => username
     *   password => password
     *   port     => mysql port
     */
    function __construct(array $params) {
        $dsn = 'mysql:host=' . $params['host'] . ';dbname=' . $params['database'];
        if(isset($params['port'])) {
            $dsn.= ';port=' . $params['port'];
        }
        if(isset($params['options'])) {
            $options = $params['options'];
            if(@$options['debug']) {
                $this->debug = true;
            }
        }
        $options = array();
        $this->connection = new \PDO($dsn,
                            $params['username'],
                            $params['password'],
                            $options);
    }

    protected function sqlType(IField $field) {
        $allow_null = true;
        $allow_default = true;

        $type_decl = "";
        $field_class = get_class($field);
        // Let's strip those idiotic namespaces.
        $ns_length = strlen("objectomatic\\fields\\");
        if(substr($field_class, 0, $ns_length) == 'objectomatic\\fields\\') {
            $field_class = substr($field_class, $ns_length);
        }
        
        switch($field_class) {
            case 'Integer':
                $type_decl =  'INTEGER';
                break;
            case 'Boolean':
                $allow_null = false;
                $type_decl =  'TINYINT NOT NULL';
                break;
            case 'NullBoolean':
                $type_decl =  'TINYINT';
                break;
            case 'Char':
                $type_decl =  'VARCHAR(' . $field->getOption('max_length') . ')';
                break;
            case 'Date':
                $type_decl =  'DATE';
                break;
            case 'DateTime':
                $type_decl =  'DATETIME';
                break;
            case 'Float':
                $type_decl =  'FLOAT';
                break;
            case 'PositiveInteger':
            case 'IPAddress':
                $type_decl =  'INTEGER UNSIGNED';
                break;
            case 'Email':
            case 'FilePath':
            case 'Text':
            case 'URL':
                $type_decl =  'TEXT';
                break;
            default:
                throw new TypeError("Unknown type '$field_class'");
        }

        if($field->getOption('primary_key')) {
            $type_decl.= ' PRIMARY KEY';
        }

        if($field->getOption('increment')) {
            $type_decl.= ' AUTO_INCREMENT';
        }
        
        if($allow_null && $field->getOption('null') !== null) {
            if(!$field->getOption('null')) {
                $type_decl.= ' NOT NULL';
            }
        }

        if($allow_default && $field->getOption('default') !== null) {
            if(is_object($field->getOption('default')) && $field->getOption('default') instanceof NullFieldValue) {
                $type_decl .= " DEFAULT 'NULL'";
            } else {
                $type_decl .= " DEFAULT '". $field->getOption('default') ."'";
            }
        }
        
        return $type_decl;
    }
    
    function loadMulti(Storable $object, $where = null) {
        $query = "SELECT * FROM `" . $object->getTypeName() . "`";

        if($where) {
            $query.= " WHERE $where";
        }

        if($this->debug) {
            echo $query . PHP_EOL;
        }

        $rowset = $this->connection->query($query);

        if(!$rowset) {
            $error = $this->connection->errorInfo();
            throw new DriverError($error[2]);
        }

        $object_class = $object->getTypeName();
        $objects = array();
        if($rowset->rowCount() < 1) {
            return false;
        } else {
            while($row = $rowset->fetch()) {
                $objects[] = new $object_class($row);
            }
            return $objects;
        }
    }
    
    function load(Storable $object, $id = null) {
        if(!$id) {
            $id = $object->getId();
        }
        
        if($id <= 0) {
            throw new KeyError("Cannot load object without ID.");
        }

        $query = "SELECT * FROM `" . $object->getTypeName() . "` WHERE id='$id'";

        if($this->debug) {
            echo $query . PHP_EOL;
        }

        $rowset = $this->connection->query($query);

        if(!$rowset) {
            $error = $this->connection->errorInfo();
            throw new DriverError($error[2]);
        }

        if($rowset->rowCount() < 1) {
            return false;
        } else {
            $object->populate($rowset->fetch());
            return true;
        }
    }

    function create(Storable $object) {
        $struct = $object->signature();
        $query = "CREATE TABLE IF NOT EXISTS `" . $object->getTypeName() . '`';

        $cols = array();
        foreach($struct as $field) {
            $cols[] = $field->getName() . " " . $this->sqlType($field);
        }

        $query.= '(' . implode(', ', $cols) . ')';

        if($this->debug) {
            echo $query . PHP_EOL;
        }

        if(!$this->connection->query($query)) {
            $error = $this->connection->errorInfo();
            throw new DriverError($error[2]);
        }

        return true;
    }
    
    function insert(Storable $object) {
        $struct = $object->signature();

        $query = "INSERT INTO `" . $object->getTypeName() . "`";

        $names = $props = array();
        /* We insert the data as a prepared statement for safety. So
         * we need to loop twice. Not awesome but it's OK.
         * Note that we're explicitly inserting. So even if an ID is
         * present, the data will get inserted as a separate object,
         * and the existing ID overwritten. */
        foreach($struct as $field) {
            if($field->getName() == 'id') continue;
            $names[] = $field->getName();
            $props[] = ":" . $field->getName();
        }
        $query.= sprintf('(%s) VALUES(%s)',
                 implode(', ', $names),
                 implode(', ', $props));

        if($this->debug) {
            echo $query . PHP_EOL;
        }
        
        $stmt = $this->connection->prepare($query);

        // And now we bind the values to the prepared statement.
        foreach($struct as $field) {
            if($field->getName() == 'id') continue;
            $stmt->bindValue(':' . $field->getName(), $field->getVal());
        }

        if(!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new DriverError($error[2]);
        } else {
            $object->setId($this->connection->lastInsertId());
            return true;
        }
    }
    
    function update(Storable $object) {
        /* The object MUST have an ID, otherwise we can't know
         * what to update. */
        if($object->getId() < 1) {
            throw new KeyError("Cannot update without ID.");
        }

        $struct = $object->signature();
        
        $query = "UPDATE `" . $object->getTypeName() . "` SET ";

        $sets = array();
        /* Again, we insert the data as a prepared statement for safety.
         * and the existing ID overwritten.
         * Unlike your usual database, we don't allow updating the id,
         * which we consider immutable.
         */
        foreach($struct as $field) {
            if($field->getName() == 'id') continue;
            $sets[] = sprintf("%s=:%s", $field->getName(), $field->getName());
        }
        $query.= implode(', ', $sets);
        $query.= ' WHERE id=' . $object->getId();
        $stmt = $this->connection->prepare($query);

        if($this->debug) {
            echo $query . PHP_EOL;
        }

        // And now we bind the values to the prepared statement.
        foreach($struct as $field) {
            if($field->getName() == 'id') continue;
            $stmt->bindValue(':' . $field->getName(), $field->getVal());
        }

        if(!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new DriverError($error[2]);
        } else {
            return true;
        }
    }

    function updateMulti(Storable $replacement, $where = null) {
        /* Unlike before, we don't care about $replacement's ID.
         * IDs are immutable. */

        $struct = $replacement->signature();
        
        $query = "UPDATE `" . get_class($replacement) . "` SET ";

        $sets = array();
        foreach($struct as $field) {
            if($field->getName() == 'id') continue; // Don't care.
            if($field->getVal() === null) continue; // We ignore those.
            $sets[] = sprintf("%s=:%s", $field->getName(), $field->getName());
        }
        $query.= implode(', ', $sets);

        if($where) {
            $query.= ' WHERE ' . $where;
        }
        
        $stmt = $this->connection->prepare($query);

        if($this->debug) {
            echo $query . PHP_EOL;
        }

        // And now we bind the values to the prepared statement.
        foreach($struct as $field) {
            if($field->getName() == 'id') continue;
            if($field->getVal() === null) continue;
            $stmt->bindValue(':' . $field->getName(), $field->getVal());
        }

        if(!$stmt->execute()) {
            $error = $stmt->errorInfo();
            throw new DriverError($error[2]);
        } else {
            return true;
        }
    }
    
    function delete(Storable $object) {
        // Same old story about the ID.
        if($object->getId() < 1) {
            throw new KeyError("Cannot delete without ID.");
        }

        $query = "DELETE FROM `" . $object->getTypeName() .
            "` WHERE id=" . $object->getId();
        if($this->debug) {
            echo $query . PHP_EOL;
        }
        
        if(!$this->connection->query($query)) {
            $error = $this->connection->errorInfo();
            throw new DriverError($error[2]);
        } else {
            $object->setId(null);
            return true;
        }
    }

    function deleteMulti(Storable $object, $where = null) {
        $query = "DELETE FROM `" . $object->getTypeName() . "`";

        if($where) {
            $query.= " WHERE $where";
        }

        if($this->debug) {
            echo $query . PHP_EOL;
        }

        if(!$this->connection->query($query)) {
            $error = $this->connection->errorInfo();
            throw new DriverError($error[2]);
        } else {
            return true;
        }
    }
    
    function drop(Storable $object) {
        $query = "DROP TABLE IF EXISTS `" . $object->getTypeName() . '`';

        if($this->debug) {
            echo $query . PHP_EOL;
        }
        
        if(!$this->connection->query($query)) {
            $error = $this->connection->errorInfo();
            throw new DriverError($error[2]);
        } else {
            return true;
        }
    }
}
