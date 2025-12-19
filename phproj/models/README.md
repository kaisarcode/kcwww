# Models

Abstract base classes for database models.

## Model.php

Abstract base class for database models using R.php (RedBeanPHP) ORM.

### Usage

```php
class User extends Model
{
    protected static string $table = 'users';
}

// Create
$user = User::create(['name' => 'John']);
$user->email = 'john@example.com';
$user->save();

// Find
$user = User::find(1);
$users = User::findAll('age > ?', [18]);

// Count
$count = User::count();
```

### Requirements

- R.php must be loaded before using models
- Database must be initialized with `R::setup($dsn)`
