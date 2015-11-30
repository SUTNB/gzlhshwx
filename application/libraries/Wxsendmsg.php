<?php

/* 
 * 发送消息
 * 时间 : 2015年11月28日
 * 作者: SUTNB
 *$result = $this->pay($postObj->FromUserName);
 * 
 */
require 'mywxconfig.php';
//获取access_token
class Wxsendmsg{
    function getWxAccessToken(){
		//1.请求url地址
                                   $mem = new Memcache(); //创建Memcache对象  
		$mem->connect('127.0.0.1', 11211); //连接Memcache服务器
		if(!($data=$mem->get(md5("access_token")))){
                                                    $data = $this->getWxAccessTokenBycurl();
                                                    $mem->set(md5("access_token"), $data, 0, $data['expires_in']);
		}
		return $data['access_token'];
    }
    //通过CURL获取access_token
    function getWxAccessTokenBycurl()
    {
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
		return  json_decode($res, true);
    }
    //回复单文本
    public function responseText($postObj,$content){
		$template = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>";
		//注意模板中的中括号 不能少 也不能多
		$fromUser = $postObj->ToUserName;
		$toUser   = $postObj->FromUserName; 
		$time     = time();
		$msgType  = 'text';
		echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
	}
                //回复用户关注事件
	public function userSubscribe($postObj){
                                    $result = $this->set_user_money($postObj);//设置用户账号,初始化金额为0.88元
                                    if($result == 1){
                                        $content = "";
                                    }else{
                                        $content = "";
                                    }
                                        $this->responseText($postObj,$content);//回复消息
	}
                    //取消用户关注事件
	public function noSubscribe($postObj, $arr){
		
	}
                  //获取毫秒时间戳
                  function get_time(){
                    $time = explode ( " ", microtime () );  
                    $time = $time [1] . ($time [0] * 1000);  
                    $time2 = explode ( ".", $time );  
                    return  $time2 [0];
                  }
                //生成带参数的二维码
                public function create_qrcode($scene_id){
                    $access_token = $this->getWxAccessToken();
                    $qrcode = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
                    $ticket_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
                    $result = $this->https_curl($ticket_url, $qrcode);
                    $info = json_decode($result, TRUE);
                    if(!isset($info['ticket'])){
                        return array('code' => -1, 'message' => '获取ticket失败');
                    }
                    $image_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($info['ticket']);
                    $imageinfo = $this->https_curl($image_url);
                    $filename = getcwd().'/qrcode/'.$scene_id.'.jpg';
                    $local_file = fopen($filename, 'w');
                    if(false !== $local_file){
                        if(false !== fwrite($local_file, $imageinfo)){
                            fclose($local_file);
                            return array('code' => 1, 'url' => $filename, 'scene_id' => $scene_id);
                        }
                        return array('code'=>-3, 'message' => '写入文件失败' );
                    }
                    return array('code'=>-2, 'message' => '创建文件失败' );
                }
                //CURL 获取数据
                function https_curl($url, $data = null){
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                    if(!empty($data)){
                        curl_setopt($curl, CURLOPT_POST, 1);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    }else{
                        curl_setopt($curl, CURLOPT_HEADER, 0);
                        curl_setopt($curl, CURLOPT_NOBODY, 0);
                    }
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($curl);
                    curl_close($curl);
                    return $output;
                }
                //回复图片消息
                public function send_qrcode($postObj, $media_id){
                    $template = "<xml>
                                        <ToUserName><![CDATA[%s]]></ToUserName>
                                        <FromUserName><![CDATA[%s]]></FromUserName>
                                        <CreateTime>%s</CreateTime>
                                        <MsgType><![CDATA[%s]]></MsgType>
                                        <Image>
                                        <MediaId><![CDATA[%s]]></MediaId>
                                        </Image>
                                        </xml>";
		//注意模板中的中括号 不能少 也不能多
		$fromUser = $postObj->ToUserName;
		$toUser   = $postObj->FromUserName;
		$time     = time();
		$msgType  = 'image';
		echo sprintf($template, $toUser, $fromUser, $time, $msgType, $media_id);
                }
                //上传二维码到临时素材
                public function add_qrcode($filepath){
                    $access_token = $this->getWxAccessToken();
                    $type = "image";
                    $filedata = array("file1"  => "@".$filepath);
                    $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$type;
                    $result = https_curl($url, $filedata);
                    $info = json_decode($result, TRUE);
                    if(!isset($info['media_id'])){
                        return array('code'=>-1, '上传失败');
                    }
                    return array('code'=>1, 'media_id'=>$info['media_id']);
                }
                //设置用户账号
                /*
                 * 如果该用户未曾关注公众号则正常初始化用户账户,生成$scene_id,初始化金额.
                 * 如果该用户关注过公众号(表中已有该用户且账户状态为取消关注),则变更账户状态为正常,金额不变.
                 */
                public function set_user_money($object){
                    $this->load->model('user_model','user');
                    $result = $this->user->check($object->FromUserName);//检查用户是否未曾关注公众号
                    if($result){
                        $data = $this->user->set_user_money($object);
                    }else{
                        $data = $this->user->update_user_money($object);
                    }
                    return $data;
                }
}

