laravel-openssl-encryption
==========================

Laravel 4 encryption package that uses the PHP **openssl** extension.

It can replace the default Laravel 4 encryption core package that uses the PHP **mcrypt** extension.

It has been created to run Laravel 4 apps on the [Google App Engine for PHP](https://developers.google.com/appengine/docs/php/) platform that currently (may 2013) does not support the mcrypt extension.

Installation
------------
Add the neoxia/laravel-openssl-encryption package to your composer.json file.

    "require": {
    	"laravel/framework": "4.0.*",
    	"neoxia/laravel-openssl-encryption": "1.0.*"
    },

Install the package.

    $ php composer.phar install

In the `app/config/app.php` file, register the `LaravelOpensslEncryptionServiceProvider` and comment the default `EncryptionServiceProvider.

    'providers' => array(
    
    	...
    	//'Illuminate\Encryption\EncryptionServiceProvider',
    	'Neoxia\LaravelOpensslEncryption\LaravelOpensslEncryptionServiceProvider',
    	...

One more thing ...

Currently, Laravel 4 checks if the PHP **mcrypt** extension is loaded and die if it is not !
So, to complete the installation, we have to bypass this check.
But unfortunately, this check is done in the `Illuminate\Foundation\start.php` script, at the heart of the framework bootstrap process.

To bypass the check, change the `start.php` script in the `vendor\laravel\framework\src\Illuminate\Foundation` folder as follow.

    if ( false and ! extension_loaded('mcrypt'))
    {
    	die('Laravel requires the Mcrypt PHP extension.'.PHP_EOL);
    
    	exit(1);
    }