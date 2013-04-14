Object'o'matic
=============

Copyright (c) 2013 Guillaume Pasquet, boss@etenil.net
http://etenil.net
Licensed under the GPLv3 license  
Version 0.1

INSTALLATION
-----------
If you use Composer, then simply add objectomatic to
your dependencies and run `composer install`.

If you wish to use the library directly, a `load.php`
file is provided within the `src` folder that will
load up all objects for you.

Finally, if you want to use objectomatic as part of an
Assegai installation, then place the folder within the
custom modules folder. You may then configure the
connection like so:

    $conf['modules'] = array(
        'objectomatic',
    );
    $conf['objectomatic'] = array(
        'host' => 'localhost',
        'database' => 'database',
        'username' => 'username',
        'password' => 'password',
    );

USAGE
----
You'll need to connect to your database first. This is
done by instanctiating a database driver. At the moment
only MySQL is supported.

    $db = new \objectomatic\MySQLPDO(array(
        'host'     => 'localhost',
        'database' => 'database',
        'username' => 'username',
        'password' => 'password',
        'port'     => 3336 // Optional
    ));

You'll need to create objects that can be stored by
objectomatic. Those need to extend the class
`objectomatic\Storable`. They may have any number
of fields, but the *id* field, which is automatically
added.

Fields are properties, but need to be instantiated
in the `_type_init()` method like so:

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

Note that afterwards, you shouldn't access the properties
directly but use their `getVal()` and `setVal()` methods.

### Fields
Fields are objects that implement the `\objectomatic\fields\IField`
interface. They are simply containers for value that also
do on-the-fly validation and can be converted to native
database types.

Objectomatic provides the following fields in the namespace
`\objectomatic\fields`:

- Binary
- Boolean
- Char
- Date
- DateTime
- Email
- FilePath
- Float
- Integer
- IPAddress
- NullBoolean
- PositiveInteger
- Text
- Time
- URL

Fields can take options, and some even require options. Field
options are passed within the field's constructor as an
associative array. Available options are:

- max_length
- null: whether the field is nullable

**A word about NULL**: NULL is both a special value in
databases and PHP. This can be problematic, so the fields use
instances of the class *NullFieldValue* whenever their database
value is set to NULL.

### Driver methods
Storable objects are considered inanimate. The driver does
the hard job through a set of methods:

- `load(Storable $object, $id = null)`: loads values into
$object. If $object has an ID set, then loads this. For
convenience, you can also specify the ID right away.
- `loadMulti(Storable $object, $where = null)`: The equivalent
of an SQL select. $object is only used for its type, and $where
is the WHERE bit of SQL. Returns an array of $objects.
- `create(Storable $object)`: Creates the table associated with
an object.
- `insert(Storable $object)`: Saves the object to the database.
Will also populates $object's id with that which was saved.
- `update(Storable $object)`: Updates $object in the databse.
$object *must* have an id.
- `updateMulti(Storable $replacement, $where = null)`: updates
multiple objects in the database. The $replacement is used for
its type and also to replace fields that aren't NullFieldValue.
- `delete(Storable $object)`: Deletes an object.
- `deleteMulti(Storable $object, $where = null)`: Deletes many
objects based on the SQL $where clause.
- `drop(Storable $object)`: Drops the object's table.

