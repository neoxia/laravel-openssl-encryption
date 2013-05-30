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