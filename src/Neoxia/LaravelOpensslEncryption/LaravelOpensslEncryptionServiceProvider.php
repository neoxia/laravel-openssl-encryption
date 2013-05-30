<?php namespace Neoxia\LaravelOpensslEncryption;

use Illuminate\Support\ServiceProvider;

class LaravelOpensslEncryptionServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['encrypter'] = $this->app->share(function($app)
		{
			return new Encrypter($app['config']['app.key']);
		});
	}

	public function boot()
	{
	    $this->package('neoxia/laravel-openssl-encryption');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('encrypter');
	}

}