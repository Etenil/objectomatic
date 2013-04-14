<?php

require('load.php');

$db = new \objectomatic\drivers\MySQLPDO(array(
          'host' => 'localhost',
          'database' => 'test',
          'username' => 'test',
          'password' => 'test',
          'options' => array('debug' => false),
      ));

class Person extends \objectomatic\Storable
{
    protected $_name;
    protected $_color;

    function _type_init() {
        $this->_name = new \objectomatic\fields\Char(array('max_length' => 64));
        $this->_color = new \objectomatic\fields\Text();
    }

    function getName() {
        return $this->_name->getVal();
    }

    function setName($val) {
        $this->_name->setVal($val);
        return $this;
    }

    function getColor() {
        return $this->_color->getVal();
    }

    function setColor($val) {
        $this->_color->setVal($val);
        return $this;
    }
}

try {
$p = new Person();

$db->drop($p);
$db->create($p);

// Let's insert an object first.
$p->setName('Mike');
$p->setColor('blue');
$db->insert($p);
$id = $p->getId();

echo "## Loading\n";
$p = new Person();
$p->setId($id);
$db->load($p);
$p->prettyPrint();

echo "## Updating\n";
$p->setColor('red');
$db->update($p);
$p->prettyPrint();

echo "## Loading Multiple\n";
$p2 = new Person();
$p2->setName('Bob');
$p2->setColor('red');
$db->insert($p2);

$p3 = new Person();
$p3->setName('John');
$p3->setColor('purple');
$db->insert($p3);

$people = $db->loadMulti(new Person(), " color='red' ");
foreach($people as $person) {
    $person->prettyPrint();
}

echo "## Updating Multiple\n";
$p = new Person();
$p->setColor('blue');
$db->updateMulti($p, " color='red' ");

$people = $db->loadMulti(new Person());
foreach($people as $person) {
    $person->prettyPrint();
}

echo "### Deleting purple\n";
$db->deleteMulti(new Person(), " color='purple' ");

$people = $db->loadMulti(new Person());
foreach($people as $person) {
    $person->prettyPrint();
}

echo "### Deleting Mike\n";
$p = new Person();
$db->load($p, 1);
$db->delete($p);

$people = $db->loadMulti(new Person());
foreach($people as $person) {
    $person->prettyPrint();
}

}
catch(Exception $e) {
    echo 'ERROR! ' . $e->getMessage() . PHP_EOL;
}