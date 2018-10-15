# Pure ORM Component

Object-Relational Mapping (ORM)

# How To Connect:

1. Instantiate the Database connection giving database settings
    ```php
    $db = new Pure\ORM\Database("mysql", "hostname", "database", "user", "password", array(PDO_attributes));
    ```
    or bind a ready PDO instance
    ```php
    $db = Pure\ORM\Database::bind($pdo);
    ```
2. It is also possible to define the database settings and apply for connection at the first time in which a query is called. This means that no database connection time is lost in pages in which there are no data queries.
    ```php
    Pure\ORM\Database::bind("mysql", "hostname", "database", "user", "password", array(PDO_attributes));
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



In order to **debug** the code, during the development phase, the error reporting should be activated

```php
Pure\ORM\Database::main()->error_reporting(true);
```



# How To define Models

The Model class let to map Schema and data to objects.
First of all, a model class declaration is required.
The define function let the developer to specify the model's properties.

```php
class User extends Model
{
    public static function define($schema)
    {
        $schema->id();
        $schema->char('username')->unique();
        $schema->char('password');
        $schema->char('email');
        $schema->boolean('active')->default(true);
    }
}
```
Once a model is defined, it is easy to map data and queries with objects.

1. Instantiate the model:
    ```php
    $model = new User;
    $model->username = 'mariorossi98';
    $model->password = 'mypassword';
    $model->email = 'mario.rossi@mai.it';
    
    $model->save();
    ```
    The save method can be used to insert or update sql data. 

    The framework will perform this choice by itself.

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

5. Get the table name:
    ```php
    $table_name = User::table();
    ```

6. Use the methods 'data' and 'json' to encode the model to a simple reprensetation that should by easily shared.

   ```php
   var_dump($model->data());
   /*
   [
     'id' => 1,
     'username' => 'mariorossi98',
     'password' => 'mypassword',
     'email' => 'mario.rossi@mai.it'
     'active' => 1
   ]
   */
   
   var_dump($model->json());
   /*
   {
     "id":1,
     "username":"mariorossi98",
     "mypassword":"root",
     "email":"mario.rossi@mai.it",
     "active":1
   }
   */
   ```

#### How To specify the model's table name

If not speficied, the table name will be the model class name + 's'. 

For example, User -> 'Users'. Otherwise, is it also possible to specify the table:

```php
class User extends Model
{
    public static function table()
    {
        return 'my_custom_table_name';
    }
    
    public static function define($schema)
    {
        $schema->id();
        $schema->char('username')->unique();
        $schema->char('password');
        $schema->char('email');
        $schema->boolean('active')->default(true);
    }
}
```

#### How To define the model's properties

```php
- $schema->id($name = 'id') // adds an id property (integer, primary, increments)
- $schema->boolean($name)
- $schema->integer($name)
- $schema->float($name)
- $schema->char($name, $size = 30)
- $schema->text($name)
- $schema->date($name)
- $schema->time($name)
- $schema->datetime($name)
- $schema->timestamps()     // adds two datetime properties: 'created_at', 'updated_at'
```

Properties can be specified by descriptors:

```php
- default($value)   // specifies the property's default value
- increments()      // 'auto_increment'
- nullable()        // by default, all properties are not nullable
- primary()         // primary key
- unique()          // unique value
- unsigned()        // valid for numbers
- link(OtherModelClass, OtherModelProperty) // foreign key
```

Property's descriptors can be concatenated like in the examples below:

```php
$schema->integer('id')->increments()->primary();
$schema->text('notes')->nullable();
$schema->char('username', 50)->unique();
$schema->boolean('active')->default(true);
```

According to define foreing keys, use the 'link' descriptor, link in this example

```php
class Book extends Model
{    
    public static function define($schema)
    {
        $schema->id();
        $schema->char('name')->unique();
        $schema->integer('bought_by')->link(User::class, 'id');
    }
}
```



# Schema Management

The Schema utility can be used to create, delete and check the existence of a named table into the database.

1. Check if a table exists:

   ```php
   $result = Pure\ORM\Schema::exists(User::class);
   ```

2. Delete a table:

   ```php
   $result = Pure\ORM\Schema::drop(User::class);
   ```

3. Create a table:

   ```php
   $result = Pure\ORM\Schema::create(User::class);
   ```

4. Is also possible to delete all the table entries:

   ```php
   $result = Pure\ORM\Schema::clear(User::class);
   ```



It is possible to fill test data at the schema's creation time. To perform this operation, the seed function in the Model should be overriden.

```php
class User extends Model
{
    public static function define($schema)
    {
        $schema->id();
        $schema->char('username')->unique();
        $schema->char('password');
        $schema->char('email');
        $schema->boolean('active')->default(true);
    }
    
    // executed only at the schema creation
    public static function seed()
    {
        $model = new User;
        $model->username = 'test';
        $model->password = 'test';
        $model->email = 'test@mail.com';
        $model->save();
        
        // ... other models
    }
}
```

