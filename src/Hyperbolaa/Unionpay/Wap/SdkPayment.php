<?php

namespace Hyperbolaa\Unionpay\Wap;

use Hyperbolaa\Unionpay\Lib\Rsa;

/**
 * Class SdkPayment
 * @package Hyperbolaa\Unionpay\Wap
 */
class SdkPayment
{
	//请求地址
	private $frontTransUrl     = 'https://gateway.95516.com/gateway/api/frontTransReq.do';
	private $backTransUrl      = 'https://gateway.95516.com/gateway/api/backTransReq.do';
	private $appTransUrl       = 'https://gateway.95516.com/gateway/api/appTransReq.do';
	private $singleQueryUrl    = 'https://gateway.95516.com/gateway/api/queryTrans.do';
	//基本信息
	private $version  = '5.0.0';
	private $sign_method = '01';
	//商户信息
	private $merchant_id;
	private $front_url;
	private $back_url;
	//订单信息
	private $order_id;
	private $txn_amt;
	private $txn_time;

	//common
	private $cert_dir;
	private $cert_path;
	private $cert_pwd;
	private $origin_query_id;



	/**
	 * 产品：跳转网关支付产品<br>
	 * 交易：消费：前台跳转，有前台通知应答和后台通知应答<br>
	 */
	public function consume()
	{
		//配置参数
		$params = [
			'version'       => $this->version,     //版本号
			'encoding'      => 'utf-8',    //编码方式
			'txnType'       => '01',		//交易类型
			'txnSubType'    => '01',	//交易子类
			'bizType'       => '000201',		//业务类型
			'certId'        => $this->getCertId(),   //签名私钥证书
			'frontUrl'      => $this->front_url,   //前台通知地址
			'backUrl'       => $this->back_url,	    //后台通知地址
			'signMethod'    => $this->sign_method,	//签名方法
			'channelType'   => '08',	//渠道类型，07-PC，08-手机
			'accessType'    => '0',	    //接入类型
			'currencyCode'  => '156', //交易币种，境内商户固定156
			//todo
			'merId'         => $this->merchant_id,  //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
			'orderId'       => $this->order_id,	    //商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
			'txnTime'       => $this->txn_time,	    //订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
			'txnAmt'        => $this->txn_amt,	    //交易金额，单位分，此处默认取demo演示页面传递的参数
		];

		//创建表单
		$params['signature'] = $this->makeSignature($params);
		//抛出表单---前台回调
		$html_form = Rsa::createAutoFormHtml($params, $this->frontTransUrl);

		return $html_form;
	}

	/**
	 * 产品：跳转网关支付产品<br>
	 * 交易：消费撤销类交易：后台消费撤销交易，有同步应答和后台通知应答<br>
	 */
	public function consumeUndo(){
		//参数
		$params = [
			'version'       => $this->version,       //版本号
			'encoding'      => 'utf-8',		         //编码方式
			'signMethod'    => $this->sign_method,   //签名方法
			'txnType'       => '31',		         //交易类型
			'txnSubType'    => '00',		         //交易子类
			'bizType'       => '000201',		     //业务类型
			'certId'        => $this->getCertId(),   //签名私钥证书
			'accessType'    => '0',		            //接入类型
			'channelType'   => '07',		        //渠道类型
			'backUrl'       => $this->back_url,     //后台通知地址

			//TODO 以下信息需要填写
			'orderId'       => $this->order_id,	    //商户订单号，8-32位数字字母，不能含“-”或“_”，可以自行定制规则，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
			'merId'         => $this->merchant_id,	    //商户代码，请改成自己的测试商户号，此处默认取demo演示页面传递的参数
			'origQryId'     => $this->origin_query_id, //原消费的queryId，可以从查询接口或者通知接口中获取，此处默认取demo演示页面传递的参数
			'txnTime'       => $this->txn_time,	    //订单发送时间，格式为YYYYMMDDhhmmss，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
			'txnAmt'        => $this->txn_amt,       //交易金额，消费撤销时需和原消费一致，此处默认取demo演示页面传递的参数

		];
		//签名
		$params['signature'] = $this->makeSignature($params);
		//异步提交---后台回调地址
		$result_arr = Rsa::post($this->backTransUrl,$params);

		//验证请求
		if(sizeof($result_arr) <= 0){
			return -1;
		}
		//验签
		if(!$this->verify($result_arr)){
			return -2;
		}

		return $result_arr;

/*		//报文处理
		if ($result_arr["respCode"] == "00"){
			//交易已受理，等待接收后台通知更新订单状态，如果通知长时间未收到也可发起交易状态查询
			//TODO
		} else if ($result_arr["respCode"] == "03" || $result_arr["respCode"] == "04" || $result_arr["respCode"] == "05" ){
			//后续需发起交易状态查询交易确定交易状态
			//TODO
		} else {
			//其他应答码做以失败处理
			//TODO
			return "失败：" . $result_arr["respMsg"] . "。<br>\n";
		}*/

	}

	/**
	 * 产品：跳转网关支付产品<br>
	 * 交易：退货交易：后台资金类交易，有同步应答和后台通知应答<br>
	 */
	public function refund(){
		//参数配置
		$params = [
			'version'       => $this->version,		    //版本号
			'encoding'      => 'utf-8',		            //编码方式
			'signMethod'    => $this->sign_method,	   //签名方法
			'txnType'       => '04',		          //交易类型-退货
			'txnSubType'    => '00',		          //交易子类
			'bizType'       => '000201',		      //业务类型
			'certId'        => $this->getCertId(),   //签名私钥证书
			'accessType'    => '0',		             //接入类型
			'channelType'   => '07',		        //渠道类型
			'backUrl'       => $this->back_url,     //后台通知地址

			//TODO 以下信息需要填写
			'orderId'       => $this->order_id,	    //商户订单号，8-32位数字字母，不能含“-”或“_”，可以自行定制规则，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
			'merId'         => $this->merchant_id,	        //商户代码，请改成自己的测试商户号，此处默认取demo演示页面传递的参数
			'origQryId'     => $this->origin_query_id, //原消费的queryId，可以从查询接口或者通知接口中获取，此处默认取demo演示页面传递的参数
			'txnTime'       => $this->txn_time,	    //订单发送时间，格式为YYYYMMDDhhmmss，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
			'txnAmt'        => $this->txn_amt,       //交易金额，退货总金额需要小于等于原消费
		];
		//签名
		$params['signature'] = $this->makeSignature($params);
		//异步提交---后台通知地址
		$result_arr = Rsa::post($this->backTransUrl,$params);
		//验证请求
		if(sizeof($result_arr) <= 0){
			return -1;
		}
		//验签
		if(!$this->verify($result_arr)){
			return -2;
		}

		return $result_arr;

/*		//报文处理
		if ($result_arr["respCode"] == "00"){
			//交易已受理，等待接收后台通知更新订单状态，如果通知长时间未收到也可发起交易状态查询
			//TODO
		} else if ($result_arr["respCode"] == "03"
			|| $result_arr["respCode"] == "04"
			|| $result_arr["respCode"] == "05" ){
			//后续需发起交易状态查询交易确定交易状态
			//TODO
		} else {
			//其他应答码做以失败处理
			//TODO
		}*/
	}

	/**
	 * 产品：跳转网关支付产品<br>
	 * 交易：交易状态查询交易：只有同步应答 <br>
	 */
	public function query(){
		//参数
		$params = [
			'version'       => $this->version,		  //版本号
			'encoding'      => 'utf-8',		  //编码方式
			'signMethod'    => $this->sign_method,		  //签名方法
			'txnType'       => '00',		      //交易类型
			'txnSubType'    => '00',		  //交易子类
			'bizType'       => '000000',		  //业务类型
			'certId'        => $this->getCertId(),   //签名私钥证书
			'accessType'    => '0',		  //接入类型
			'channelType'   => '07',		  //渠道类型

			//TODO 以下信息需要填写
			'orderId'   => $this->order_id,	   //请修改被查询的交易的订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数
			'merId'     => $this->merchant_id, //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
			'txnTime'   => $this->txn_time,	   //请修改被查询的交易的订单发送时间，格式为YYYYMMDDhhmmss，此处默认取demo演示页面传递的参数
		];
		//签名
		$params['signature'] = $this->makeSignature($params);

		//异步提交
		$result_arr = Rsa::post($this->singleQueryUrl,$params);

		//验证请求
		if(sizeof($result_arr) <= 0){
			return -1;
		}
		//验签
		if(!$this->verify($result_arr)){
			return -2;
		}

		//返回信息，等待报文处理
		return $result_arr;

		//报文处理
/*		if ($result_arr["respCode"] == "00"){
			if ($result_arr["origRespCode"] == "00"){
				//交易成功
				//TODO
			} else if ($result_arr["origRespCode"] == "03"
				|| $result_arr["origRespCode"] == "04"
				|| $result_arr["origRespCode"] == "05"){
				//后续需发起交易状态查询交易确定交易状态
				//TODO
			} else {
				//其他应答码做以失败处理
				//TODO
			}
		} else if ($result_arr["respCode"] == "03"
			|| $result_arr["respCode"] == "04"
			|| $result_arr["respCode"] == "05" ){
			//后续需发起交易状态查询交易确定交易状态
			//TODO
		} else {
			//其他应答码做以失败处理
			//TODO
		}*/
	}

	/**
	 *  验签
	 */
	public function verify($data=null){
		if(!$data){
			if (empty($_POST) && empty($_GET)) {
				return false;
			}
			$data = $_POST ?  : $_GET;
		}

		return Rsa::verify($data,$this->cert_dir);
	}

	/**
	 * 生成签名
	 */
	private function makeSignature($params){
		return  Rsa::getParamsSignatureWithRSA($params,$this->cert_path,$this->cert_pwd);
	}

	/**
	 * 获取秘钥ID
	 */
	private function getCertId(){
		return Rsa::getCertId($this->cert_path,$this->cert_pwd);
	}



	public function setMerId($value)
	{
		$this->merchant_id = $value;
		return $this;
	}

	public function setNotifyUrl($value)
	{
		$this->back_url = $value;
		return $this;
	}

	public function setReturnUrl($value)
	{
		$this->front_url = $value;
		return $this;
	}

	public function setOrderId($value)
	{
		$this->order_id = $value;
		return $this;
	}

	public function setTxnAmt($value)
	{
		$this->txn_amt = $value;
		return $this;
	}

	public function setTxnTime($value)
	{
		$this->txn_time = $value;
		return $this;
	}

	public function setCertDir($value)
	{
		$this->cert_dir = $value;
		return $this;
	}

	public function setCertPath($value)
	{
		$this->cert_path = $value;
		return $this;
	}

	public function setCertPwd($value)
	{
		$this->cert_pwd = $value;
		return $this;
	}


	public function setOriginQueryId($value)
	{
		$this->origin_query_id = $value;
		return $this;
	}

}
