<?php

namespace App\Library\Skeleton\Billing;

use Illuminate\Support\ServiceProvider;
use Config;

class PaymentGatewayServiceProvider extends ServiceProvider
{
	public $gateways;
	public $plugin_dir;
    /**
     * Register services.
     *
     * @return void
     */
	
	public function init()
	{
		$this->gateways = array();
		$this->plugin_dir = $plugin_dir = __DIR__.'/PaymentGateways/';
		$folders = scandir($plugin_dir);
		foreach($folders as $folder){
			
			if($folder == '..' || $folder == '.' || !is_dir($plugin_dir.'/'.$folder))continue;	
			if(!file_exists($plugin_dir.'/'.$folder.'/config.php'))continue;
			$gt = include($plugin_dir.'/'.$folder.'/config.php');
			$this->gateways[$gt['name']] = $gt;
		}
		
		Config::set('payment-gateways', $this->gateways);
	}
	
    public function register()
    {
		$this->init();
		
		/*
		foreach($this->gateways as $gt){
			$this->app->singleton($gt['name'], $gt['class']);
		}
		*/
	}

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
		foreach($this->gateways as $gt){
			$this->loadRoutesFrom($this->plugin_dir.'/'.$gt['path'].'/routes.php');
		}
    }
}
