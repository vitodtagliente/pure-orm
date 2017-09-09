# Pure ORM Component

Object-Relational Mapping (ORM)

# How To Connect:

1. Instantiate the Database connection giving database settings
    ```php
    $db = new Pure/ORM/Database("mysql", "hostname", "database", "user", "password", array(PDO_attributes));
    ```
    or bind a ready PDO instance
    ```php
    $db = Pure\ORM\Database::bind($pdo);
    ```
2. It is also possible to define the database settings and apply for connection at the first time in which a query is called. This means that no database connection time is lost in pages in which there are no data queries.
    ```php
    Pure/ORM/Database::bind("mysql", "hostname", "database", "user", "password", array(PDO_attributes));
    ```
3. After the step 1 or 2, the database instance can be globally accessed by the singleton pattern:
    ```php
    $db = Pure\ORM\Database::main();
    ```
4. It is possible to change the current database instance:
    ```php
    $db1 = new Pure\ORM\Database(....);
    $db2 = new Pure\ORM\Database(....);

    $current = Pure\ORM\Database::main(); // this refers to $db2
    Pure\ORM\Database::change($db1);
    $current = Pure\ORM\Database::main(); // this refers to $db1
    ```
5. Close the connection by:
    ```php
    $db->close();
    ```    

## How To execute queries:

1. Getting a row:
    ```php
    $data = $db->fetch("SELECT email, username FROM users WHERE username = ?", array("Mario"));
    ```
    Or
    ```php
    $data = $db->select("users", array( 'email', 'username' ), "username = Mario" );
    ```
2. Getting multiple rows:
    ```php
    $data = $db->fetchAll("SELECT id, username FROM users");
    ```
    Or
    ```php
    $data = $db->selectAll("users", array( 'email', 'username' ), $condition);
    ```
3. Insert a row:
    ```php
    $result = $db->insert("users", array( "name" => "Mario", "email" => "mario.rossi@email.com"));
    ```
    $result will be true or false
4. Update existing row:
    ```php
    $result = $db->update("users", "id = 1", array( "name" => "Roberto" ) );
    ```
5. Remove rows:
    ```php
    $result = $db->delete("users", "id = 1");
    ```

# Schema Management

The Schema utility can be used to create, delete and check the existence of a named table into the database.

1. Check if a table exists:
    ```php
    $result = Pure\ORM\Schema::exists('users');
    ```
2. Delete a table:
    ```php
    $result = Pure\ORM\Schema::drop('users');
    ```
3. Create a table:
    ```php
    $result = Pure\ORM\Schema::create($query);
    ```
    where $query can be
        1. string like "CREATE TABLE ..."
        2. SchemaBuilder instance

# Schema Builder

The SchemaBuilder class let to define and create tables by code

1. Instantiate the SchemaBuilder and define the table's name
    ```php
    $schema = new Pure\ORM\SchemaBuilder('users');
    ```
2. Add columns
    ```php
    $schema->add($name, $type, $expression = null)
    ```
    Let me show an example
    ```php
    $schema->add('id', 'INT');
    $schema->add('name', 'VARCHAR(30)', 'NOT NULL');
    $schema->add('username', 'VARCHAR(30)', 'NOT NULL');
    $schema->unique('username'); // username must be unique
    $schema->increments('id'); // auto_increment
    $schema->primary('id'); // set the primary key
    $schema->add('password', 'VARCHAR(30)', 'NOT NULL');
    ```
    In this way the table schema can be defined using code.
    Each SchemaBuilder instance produces a query:
    ```php
    $query = $schema->query();
    Pure\ORM\Schema::create( $query ); // create the table
    ```
    The $query of this example will be this:
    ```sql
    CREATE TABLE users ( id INT  not null auto_increment , name VARCHAR(30) NOT NULL, username VARCHAR(30) NOT NULL, password VARCHAR(30) NOT NULL, CONSTRAINT pk_id PRIMARY KEY ( id ), CONSTRAINT uc_username UNIQUE ( username ) )
    ```

# How To define Models

The Model class let to map Schema and data to objects.
First of all, a model class declaration is required.
Inside the class constructor it is necessary to define and register all the required fields.
```php
    class User extends Pure\ORM\Model
    {
        function _constructor(){

            $this->col('id');
            $this->col('name');
            $this->col('username');
            $this->col('password');

            $this->id('id'); // specify the id field
        }
    }
```
Once a model is defined, it is easy to map data and queries with objects.

1. Instantiate the model:
    ```php
    $model = new User();
    $model->name = 'Mario';
    $model->password = '****';
    $model->username = 'mariorossi98';

    $model->save();
    ```
    The save method can be used to insert or update sql data.
2. Find models:
    ```php
    $model = User::find('id = 1');
    ```
3. Delete data:
    ```php
    $model = User::find('id = 1');
    // ....
    $model->erase();
    ```
4. Retrieve multiple models:
    ```php
    $models = User::all($condition = null);
    ```
