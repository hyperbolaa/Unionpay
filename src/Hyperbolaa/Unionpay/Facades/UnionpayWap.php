<?php
/**
 * wap支付门面入口
 */
namespace Hyperbolaa\Unionpay\Facades;

use Illuminate\Support\Facades\Facade;

class UnionpayWap extends Facade
{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'unionpay.wap';
	}
}
