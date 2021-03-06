<?php

/* 
 * 发送消息
 * 时间 : 2015年11月28日
 * 作者: SUTNB
 *$result = $this->pay($postObj->FromUserName);
 * 
 */
require 'wxsendconfig.php';
//获取access_token
class Wxsendmsg{
    function getWxAccessToken(){
		//1.请求url地址
                                   $mem = new Memcache(); //创建Memcache对象  
		$mem->connect('127.0.0.1', 11211); //连接Memcache服务器
                                   //$mem->delete(md5("access_token"));
		if(!($data=$mem->get(md5("access_token")))){
                                                    $data = $this->getWxAccessTokenBycurl();
                                                    $mem->set(md5("access_token"), $data, 0, 3600);
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
        

                    //取消用户关注事件
	public function unSubscribe($postObj, $arr){
		$this->load->model('user_model','user');
                                   $this->update_user_status($postObj, 3);//设置用户为取消关注(3)
	}
                  //获取毫秒时间戳
                  function get_time(){
                    $time = explode ( " ", microtime () );  
                    $time = $time [1] . ($time [0] * 1000);  
                    $time2 = explode ( ".", $time );  
                    return  $time2 [0];
                  }
                //生成带参数的二维码
                public function create_qrcode($scene_id, $openid){
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
                            //生成二维码海报－－－－－－－－－－－－－－－－－
                            $this->create_poster($scene_id, $openid);
                            return ['code' => 1, 'url' => $filename, 'scene_id' => $scene_id];
                        }
                        return array('code'=>-3, 'message' => '写入文件失败' );
                    }
                    return array('code'=>-2, 'message' => '创建文件失败' );
                }
                //生成海报
                function create_poster($scene_id, $openid){
                    $user = $this->get_userinfo($openid);
                    //$this->responseTextBycustom($openid, 'test');
                    $filename = getcwd().'/qrcode/'.$scene_id.'.jpg';
                    $this->thumb_image($filename);//缩略二维码
                    $this->add_text_image($user['nickname'], '/var/www/nbwx/image/ditu.jpg', $scene_id);//添加文字
                    $this->add_qrcode_image('/var/www/nbwx/image/'.$scene_id.'.jpg', $filename);
                }
                //图片加文字水印
                function add_text_image($content, $src, $scene_id){
                        $info = getimagesize($src);
                        //print_r($info);die;
                        $type = image_type_to_extension($info[2], false);
                        $fun = "imagecreatefrom".$type;
                        $image = $fun($src);
                        //操作图片
                        $font = getcwd().'/application/libraries/'."fonts-japanese-gothic.ttf";
                        $col = imagecolorallocatealpha($image, 255, 255, 0, 10);
                        imagettftext($image, 20, 0, 15, 520, $col, $font, $content);
                        //保存图片
                        imagejpeg($image, '/var/www/nbwx/image/'.$scene_id.'.jpg');
                        //销毁图片
                        imagedestroy($image);
                }
                //图片拼接
                function add_qrcode_image($src, $src_head){
                     //打开图片
                    $info = getimagesize($src);
                    $type = image_type_to_extension($info[2], false);
                    $fun = "imagecreatefrom".$type;
                    $image = $fun($src);
                    
                    $info_head = getimagesize($src_head);
                    //print_r($info_head);die;
                    $type_head = image_type_to_extension($info_head[2], false);
                    $func = "imagecreatefrom".$type_head;
                    $image_head = $func($src_head);
                    imagecopymerge($image, $image_head, 250,588, 0, 0, $info_head[0], $info_head[1], 100);
                    imagedestroy($image_head);
                    //保存图片
                    imagejpeg($image, $src_head);
                    //销毁图片
                    imagedestroy($image);
                }
                //缩略图片
                function thumb_image($src){
                        //打开图片
                        $info = getimagesize($src);
                        //print_r($info);die;
                        $type = image_type_to_extension($info[2], false);
                        $fun = "imagecreatefrom".$type;
                        $image = $fun($src);
                        $image_thumb = imagecreatetruecolor(250, 250);
                        imagecopyresampled($image_thumb, $image, 0, 0, 0, 0, 250, 250, $info[0], $info[1]);
                        imagedestroy($image);
                        //保存图片
                        imagejpeg($image_thumb, $src);
                        //销毁图片
                        imagedestroy($image_thumb);
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
                public function upload_qrcode($filepath){
                    $access_token = $this->getWxAccessToken(); 
                    $type = "image";
                    $filedata = array("media"  => "@".$filepath);
                    $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$type; 
                    $result = $this->https_curl($url, $filedata);
                    $info = json_decode($result, TRUE);
                    if(!isset($info['media_id'])){
                        return array('code'=>-1, 'message'=>'上传失败');
                    }
                    return array('code'=>1, 'media_id'=>$info['media_id']);
                }
                //客服回复文本接口
                public function responseTextBycustom($openid, $content){
                $txt = '{ "touser" :"'. $openid.'","msgtype" : "text" ,'.'"text": {"content": "'.$content.'"} }';
                $access_token = $this->getWxAccessToken(); 
                //return array('code'=>$info['errcode'], 'errmsg'=>$access_token);
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                $result = $this->https_curl($url, $txt);
                $info = json_decode($result, TRUE);
                        return array('code'=>$info['errcode'], 'errmsg'=>$info['errmsg']);
                }
                //获取用户详细信息
                public function get_userinfo($openid){
                    $access_token = $this->getWxAccessToken(); 
                    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid;
                    $result = $this->https_curl($url);
                    $info = json_decode($result, TRUE);
                    return $info;
                }
                    public function get_user_byopenid($openid){
                    $mem_user = new Memcache(); //创建Memcache对象  
                    $mem_user->connect('127.0.0.1', 11211); //连接Memcache服务器
                    //$mem->delete(md5("access_token"));
                    if(!($data=$mem_user->get(md5($openid)))){
                             $data = $this->get_userinfo($openid);
                             $mem_user->set(md5($openid), $data, 0, 7200);
                    }
                    return $data;
                }
                    //创建菜单
                    public function create_menu($data)
                    {
                        $access_token = $this->getWxAccessToken(); 
                        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
                         $res = $this->https_curl($url, $data);
                        $result = json_decode($res, true);
                         return $result['errcode'];
                    }      
}

