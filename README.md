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
    where $query can be:
        * string:
            ```php
            $query = "CREATE TABLE users ....";
            ```
        * SchemaBuilder instance

# Schema Builder
