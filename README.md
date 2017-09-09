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
3. Before the step 1 or 2, the database instance can be globally accessed by the singleton pattern:
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

# How To manage queries at low level:

1. Getting a row
    ```php
    $db->fetch("SELECT email, username FROM users WHERE username =?", array("Mario"));
    ```
    Or
    ```php
    $db->select("users", array( 'email', 'username' ), "username = Mario" );
    ```
2. Getting multiple rows
    ```php
    $db->fetchAll("SELECT id, username FROM users");
    ```
3. Inserting a row
    ```php
    $db->insert("users", array( "name" => "Mario", "email" => "mario.rossi@email.com"));
    ```
4. Updating existing row
    ```php
    $db->update("users", "id = 1", array( "name" => "Roberto" ) );
    ```
