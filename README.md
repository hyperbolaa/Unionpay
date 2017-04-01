## Unionpay & laravel & 银联支付

#### 交易类型
 * 00：查询交易，
 * 01：消费，
 * 02：预授权，
 * 03：预授权完成，
 * 04：退货，
 * 05：圈存，
 * 11：代收，
 * 12：代付，
 * 13：账单支付，
 * 14：转账（保留），
 * 21：批量交易，
 * 22：批量查询，
 * 31：消费撤销，
 * 32：预授权撤销，
 * 33：预授权完成撤销，
 * 71：余额查询，
 * 72：实名认证-建立绑定关系，
 * 73：账单查询，
 * 74：解除绑定关系，
 * 75：查询绑定关系，
 * 77：发送短信验证码交易，
 * 78：开通查询交易，
 * 79：开通交易，
 * 94：IC卡脚本通知 ,
 * 95：查询更新加密公钥证书
 
#### 产品类型:bizType
 * 依据实际业务场景填写 默认取值：000000 具体取值范围：
 * 000201：B2C 网关支付
 * 000301：认证支付 2.0
 * 000302：评级支付
 * 000401：代付
 * 000501：代收
 * 000601：账单支付
 * 000801：跨行收单
 * 000901：绑定支付
 * 001001：订购
 * 000202：B2B
 
#### 接入类型：accessType
 * 0：商户直接接入
 * 1：收单机构接入
 * 2：平台商接入

#### 渠道类型：channelType
 * 05：语音
 * 07：互联网
 * 08：移动
 * 16：数字机顶盒

#### 应答码：respCode
 * 00：成功
 * 01-09：银联全渠道系统原因导致的错误
 * 10-29：商户端上送保温格式检查导致的错误
 * 30-59：商户端相关业务检查导致的错误
 * 60-89：持卡人/发卡行 相关问题导致的错误
 * 90-99：预留
 

 
#### 备注
    version5.0.0 与 version5.1.0 验签方式不一样
 
 
#### 安装
    composer require hyperbolaa/unionpay dev-master
 
#### laravel 配置
     'providers' => [
         // ...
         Hyperbolaa\Unionpay\UnionpayServiceProvider::class,
     ]
  
#### 生成配置文件
    运行 `php artisan vendor:publish` 命令，
    发布配置文件到你的项目中。
 
#### app代码使用
    $unionpay = app('unionpay.mobile');
    $unionpay->setOrderId('order_id');
    $unionpay->setTxnAmt('order_amount');
    $unionpay->setTxnTime('req_time');
    
    //返回签名后的支付参数给移动端的sdk-》{539512046523081531300}
    return $unionpay->consume();
    
#### wap代码使用
    $unionpay = app('unionpay.wap');
    $unionpay->setOrderId('order_id');
    $unionpay->setTxnAmt('order_amount');
    $unionpay->setTxnTime('req_time');
    
    //返回一个表单
    return $unionpay->consume();
    
#### 异步通知
    	public function unionpayNotify()
    	{
    		if (! app('unionpay.mobile')->verify()) {
    			Log::notice('unionpay notify post data verification fail.', [
    				'data' => Request::instance()->getContent()
    			]);
    			return 'fail';
    		}
    
    		// 判断通知类型。
    		if (Input::get('respCode') == '00') {
    				// TODO: 支付成功，取得订单号进行其它相关操作。
    				Log::debug('unionpay notify get data verification success.', [
    					'out_trade_no'  => Input::get('orderId'),
    					'trade_no'      => Input::get('queryId')
    				]);
    		}
    
    		return 'success';
    	}

 ## happy coding
 
 