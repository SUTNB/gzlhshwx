<?php

/* 
 * 发送接口红包
 * 时间 : 2015年11月28日
 * 作者: SUTNB
 *$result = $this->pay($postObj->FromUserName);
 * 
 */	
require 'wxhbconfig.php';
class Wxhb{
    public $mch_id = MCH_ID;//商户Id
    public $wxappid = WXAPPID;//公众账号ID
    public $send_name = SEND_NAME;///红包发送者名称
    public $act_name = ACT_NAME;//活劢名称
    public $remark = REMARK;//备注信息
    public $client_ip = CLIENT_IP;///调用接口的机器 Ip 地址
    public $wishing = WISHING;//红包祝福诧
    
    public function curl_post_ssl($url, $vars, $second=30,$aHeader=array()){
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
	  
		//以下两种方式需选择一种
	  
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/application/controllers/apiclient_cert.pem');
		//return getcwd().'/application/controllers/apiclient_cert.pem';
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/application/controllers/apiclient_key.pem');
	  
		curl_setopt($ch,CURLOPT_CAINFO,'PEM');
		curl_setopt($ch,CURLOPT_CAINFO,getcwd().'/application/controllers/rootca.pem');
	  
		//第二种方式，两个文件合成一个.pem文件
		//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
	  
		if( count($aHeader) >= 1 ){
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	  
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		if($data){
		    curl_close($ch);
		    return $data;
		}
		else { 
		    $error = curl_errno($ch);
		    //return $error;
		    //echo "call faild, errorCode:$error\n"; 
		    curl_close($ch);
		    return false;
		}
    }
	/**
 	* 生成随机数
	 */     
	public function great_rand(){
	    $str = '1234567890abcdefghijklmnopqrstuvwxyz';
                      $t1 = '';
	    for($i=0;$i<30;$i++){
		$j=rand(0,35);
		$t1 .= $str[$j];
	    }
	    return $t1;    
	}
	/*
	例如：
	appid：    wxd111665abv58f4f
	mch_id：    10000100
	device_info：  1000
	Body：    test
	nonce_str：  ibuaiVcKdpRxkhJA
	第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
	stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
	d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
	第二步：拼接支付密钥：
	stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
	sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
	9CF3B7"
	*/
	protected function get_sign($data){
	    	$key = "niubenkaifagzlhshweixinpingtai12";
		ksort($data);
                                  //return $data;
		$stringA = '';
		foreach($data as $k => $v) :
			$stringA .= $k."=".$v.'&';
		endforeach;
		//return $stringA;
		$stringSignTemp = $stringA.'key='.$key;
                                   //return $stringSignTemp;
		return strtoupper(md5($stringSignTemp));
	}
      /**
     * 微信支付
     * @param string $openid 用户openid
     */
    public function pay($re_openid, $to_amount)
    {
	$data['mch_id'] = $this->mch_id;//商户Id
	$data['mch_billno'] = $data['mch_id'].date('YmdHis').rand(1000,9999);//订单号	
	$data['nonce_str'] = $this->great_rand();//随机字符串
	$data['wxappid'] = $this->wxappid;//公众账号ID
	$data['send_name'] = $this->send_name;//红包发送者名称
	$data['total_amount'] = $to_amount;//付款金额，单位分
	$data['act_name'] = $this->act_name;//活劢名称
	$data['remark'] = $this->remark;//备注信息
	$data['client_ip'] = $this->client_ip;//调用接口的机器 Ip 地址
	$data['wishing'] = $this->wishing;//红包祝福诧
	$data['total_num'] = 1;//红包数
	$data['re_openid'] = $re_openid;
	$sign = $this->get_sign($data);
	//return $sign.'----'.$data['nonce_str'].'----'.$data['mch_billno'];
	$template = "<xml>
	<sign><![CDATA[%s]]></sign>
	<mch_billno><![CDATA[%s]]></mch_billno>
	<mch_id><![CDATA[%s]]></mch_id>
	<wxappid><![CDATA[%s]]></wxappid>
	<send_name><![CDATA[%s]]></send_name>
	<re_openid><![CDATA[%s]]></re_openid>
	<total_amount><![CDATA[%s]]></total_amount>
	<total_num><![CDATA[%s]]></total_num>
	<wishing><![CDATA[%s]]></wishing>
	<client_ip><![CDATA[%s]]></client_ip>
	<act_name><![CDATA[%s]]></act_name>
	<remark><![CDATA[%s]]></remark>
	<nonce_str><![CDATA[%s]]></nonce_str>
	</xml>";
                 $postXml = sprintf($template, $sign, $data['mch_billno'], $data['mch_id'], $data['wxappid'], $data['send_name'], $data['re_openid'],$data['total_amount'], $data['total_num'], $data['wishing'], $data['client_ip'], $data['act_name'], $data['remark'], $data['nonce_str']);     
	$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
	//$objet = simplexml_load_string($postXml);
	//return  $objet->wxappid;
                 $this_header = array(
                    "content-type: application/x-www-form-urlencoded; 
                    charset=UTF-8"
                );
	$responseXml = $this->curl_post_ssl($url, $postXml, 30, $this_header);
                //用作结果调试输出
	/*if($responseXml)
	{
		return $responseXml;
	}else{
		return "false";
	}*/
                //echo htmlentities($responseXml,ENT_COMPAT,'UTF-8');
    	$responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
    	return $responseObj->return_code;
	//return "测试";
    }	
}

