<?php
namespace Hyperbolaa\Unionpay;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class UnionpayServiceProvider extends ServiceProvider
{

    /**
     * boot process
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
	protected function setupConfig()
	{
		$source_config = realpath(__DIR__ . '/../../config/config.php');

		if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
			$this->publishes([
				$source_config => config_path('unionpay.php'),
			]);
		} elseif ($this->app instanceof LumenApplication) {
			$this->app->configure('unionpay');
		}

		$this->mergeConfigFrom($source_config, 'unionpay');
	}

    /**
     * Register the service provider.
     *
     * @return void
     */
	public function register()
	{
		$this->app->bind('unionpay.mobile', function ($app)
		{
			$unionpay = new Mobile\SdkPayment();
			$unionpay->setMerId($app->config->get('unionpay.merchant_id'))
				->setNotifyUrl($app->config->get('unionpay.app_notify_url'))
				->setCertDir($app->config->get('unionpay.cert_dir'))
				->setCertPath($app->config->get('unionpay.cert_path'))
				->setCertPwd($app->config->get('unionpay.cert_pwd'));
			return $unionpay;
		});
		$this->app->bind('unionpay.wap', function ($app)
		{
			$unionpay = new Wap\SdkPayment();
			$unionpay->setMerId($app->config->get('unionpay.merchant_id'))
				->setNotifyUrl($app->config->get('unionpay.wap_notify_url'))
				->setReturnUrl($app->config->get('unionpay.wap_return_url'))
				->setCertDir($app->config->get('unionpay.cert_dir'))
				->setCertPath($app->config->get('unionpay.cert_path'))
				->setCertPwd($app->config->get('unionpay.cert_pwd'));
			return $unionpay;
		});
	}

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'unionpay.mobile',
            'unionpay.wap',
        ];
    }
}
