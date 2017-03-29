<?php
/**
 * 手机支付门面入口
 */
namespace Hyperbolaa\Unionpay\Facades;

use Illuminate\Support\Facades\Facade;

class UnionpayMobile extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'unionpay.mobile';
    }
}
