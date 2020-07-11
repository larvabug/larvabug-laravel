<p align="center">
  <img width="130" src="http://larvabug.local/assets/images/larvabug-logo.png">
</p>

# LarvaBug
Laravel 5.2+ package for logging errors to [larvabug.com](https://www.larvabug.com)

[![Software License](https://poser.pugx.org/larvabug/larvabug-laravel/license.svg)](LICENSE.md)
[![Latest Version on Packagist](https://poser.pugx.org/larvabug/larvabug-laravel/v/stable.svg)](https://packagist.org/packages/larvabug/larvabug-laravel)
[![Total Downloads](https://poser.pugx.org/larvabug/larvabug-laravel/d/total.svg)](https://packagist.org/packages/larvabug/larvabug-laravel)


## Installation 
You can install the package through Composer.
```bash
composer require larvabug/larvabug-laravel
```

Add the LarvaBug service provider and facade in config/app.php:
```php

'providers' => [
    LarvaBug\Provider\ServiceProvider::class,
],

'aliases' => [
    'LarvaBug' => \LarvaBug\Facade\LarvaBug::class
],

```
Then publish the config file of the larvabug using artisan command.
```bash
php artisan vendor:publish --provider="LarvaBug\Provider\ServiceProvider"
```
File (`config/larvabug.php`) contains all the configuration related to bug reporting.

Note: by default production and local environments will report errors. To modify this edit your larvabug configuration environment array.

## Environment variables
 Need to define two environment variable and their values.
 
 ```
 LB_PROJECT_ID=
 LB_SECRET=
 ```
Get these variable values under setting page of the project at [larvabug.com](https://www.larvabug.com)

# Reporting unhandled exceptions

Add larvabug reporting to `app/Exceptions/Handler.php` file of laravel.

```php
public function report(Exception $exception)
{
    LarvaBug::report($exception);
    parent::report($exception);
}
``` 

# Reporting handled exceptions

In case of handled exceptions, exceptions can be reported to larvabug by using `LarvaBug` facade:

```php
try {
    //Code here
 }catch (\Exception $exception){
    LarvaBug::report($exception); 
}
``` 

#Logging specific data

To log specific data to LarvaBug use `log()` method of LarvaBug facade:

```php
$metaData = ['custom_data' => ['x','y','z']]; //Array
LarvaBug::log('Log message here',$metaData);
```

# User feedback

LarvaBug provides the ability to collect feedback from user when an error occurs, 
LarvaBug shows feedback collection page and then feedback is added with the exception report,
to enable this functionality simply need to add `collectFeedback()` method in `render()` method of `app/Exceptions/Handler.php`

```php
public function render($request, Exception $exception)
{
    if (LarvaBug::shouldCollectFeedback($exception) && !$request->wantsJson()) {
        return LarvaBug::collectFeedback();
    }
    return parent::render($request, $exception);
}
```

## License
The larvabug package is open source software licensed under the [license MIT](http://opensource.org/licenses/MIT)
