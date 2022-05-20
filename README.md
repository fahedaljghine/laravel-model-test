# Add test to Eloquent models

This package gives you the ability to generate a bunch of tests for all models by one command.

## Contact Me

You can check all of my information
by [Checking my website](https://fahedaljghine.com/).

## Installation

You can install the package via composer:

```bash

composer require fahedaljghine/laravel-model-test
```

you must publish package assets with:

```bash
php artisan vendor:publish --provider="Fahedaljghine\ModelTest\ModelTestServiceProvider"
```

This is the contents of the file which will be published at `config/model-test.php`

```php
<?php

return [

    /*
     * the models you need to generate tests to
     */
    'models' => [
        //'Model1' , 'Model2'
    ],

    /*
     * if your models routes are protected with auth guard middleware
     * and want to test that against your model set this to true
     */
    'auth_user' => true,

    /*
     * if you are using spatie/laravel-permission in your project
     * and want to test that against your model set this to true
     */
    'laravel-permissions' => false
    
];
```

### Prerequisites

you need to make sure you have factory for each model before testing


## Usage

Add models you want to test to config file like this :

```php
    /*
     * the models you need to generate tests to
     */
    'models' => [
        'Post' , 
        'Comment',
    ],
```

### Generate tests

You can generate tests for given models by running this command

```php
php artisan test:generate
```

this will generate a test file for each model which gives you the ability to extend the tests or overwrite them

### Ready for testing!

You can run tests for `Post` model by running this command

```php
php artisan test --filter=PostsTest
```

or you can test all by running

```php
php artisan test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
