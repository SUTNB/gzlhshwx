<?php

/* 
 * 发送消息
 * 时间 : 2015年11月28日
 * 作者: SUTNB
 *$result = $this->pay($postObj->FromUserName);
 * 
 */
require 'mawxconfig.php';
class Wxsendmsg{
    function getWxAccessToken(){
		//1.请求url地址
		$appid = APPID;
		$appsecret =  APPSECRET;
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
		//2初始化
		$ch = curl_init();
		//3.设置参数
		curl_setopt($ch , CURLOPT_URL, $url);
		curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
		//4.调用接口 
		$res = curl_exec($ch);
		if( curl_errno($ch) ){
			var_dump( curl_error($ch) );
		}
                                    //5.关闭curl
		curl_close( $ch );
		$arr = json_decode($res, true);
		var_dump( $arr );
    }
    
}

