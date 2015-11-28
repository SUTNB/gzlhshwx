<?php
class Hello extends CI_Controller{
    public function index(){
        $this->load->library('wxsendmsg');
        $this->wxsendmsg->getWxAccessToken();
		//获得参数 signature nonce token timestamp echostr
//		$nonce     = $_GET['nonce'];
//		$token     = 'gzlhsh';
//		$timestamp = $_GET['timestamp'];
//		$echostr   = $_GET['echostr'];
//		$signature = $_GET['signature'];
//		//形成数组，然后按字典序排序
//		$array = array();
//		$array = array($nonce, $timestamp, $token);
//		sort($array);
//		//拼接成字符串,sha1加密 ，然后与signature进行校验
//		$str = sha1( implode( $array ) );
//		if( $str  == $signature && $echostr ){
//			//第一次接入weixin api接口的时候
//			echo  $echostr;
//			exit;
//		}else{
//			$this->reponseMsg();
//		}
	}
	// 接收事件推送并回复
	public function reponseMsg(){
		//1.获取到微信推送过来post数据（xml格式）
		$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
		//2.处理消息类型，并设置回复类型和内容
		/*<xml>
		<ToUserName><![CDATA[toUser]]></ToUserName>
		<FromUserName><![CDATA[FromUser]]></FromUserName>
		<CreateTime>123456789</CreateTime>
		<MsgType><![CDATA[event]]></MsgType>
		<Event><![CDATA[subscribe]]></Event>
		</xml>*/
		$postObj = simplexml_load_string( $postArr );
		//$postObj->ToUserName = '';
		//$postObj->FromUserName = '';
		//$postObj->CreateTime = '';
		//$postObj->MsgType = '';
		//$postObj->Event = '';
		// gh_e79a177814ed
		//判断该数据包是否是订阅的事件推送
		if( strtolower( $postObj->MsgType) == 'event'){
			//如果是关注 subscribe 事件
			if( strtolower($postObj->Event == 'subscribe') ){
				//回复用户消息(纯文本格式)	
				$toUser   = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time     = time();
				$msgType  =  'text';
				$content  = '欢迎关注我们的微信公众账号';
				$template = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>";
				$info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
				echo $info;
				/*<xml>
				<ToUserName><![CDATA[toUser]]></ToUserName>
				<FromUserName><![CDATA[fromUser]]></FromUserName>
				<CreateTime>12345678</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[你好]]></Content>
				</xml>*/
			

			}
	}
	if(strtolower($postObj->MsgType) == 'text' && trim($postObj->Content)=='红包测试'){
                                                                      $this->load->library('wxhb');
				$result = $this->wxhb->pay($postObj->FromUserName,100);
				//$result = '服务号正在维护升级,给你带来的不便敬请谅解,后续将会有更多功能上线,让我们拭目以待!';
				//注意模板中的中括号 不能少 也不能多
				$template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
				$fromUser = $postObj->ToUserName;
				$toUser   = $postObj->FromUserName; 
				$time     = time();
				$msgType  = 'text';
				echo sprintf($template, $toUser, $fromUser, $time, $msgType, $result);

		}else{
			switch( trim($postObj->Content) ){
				case 1:
					$content = '您输入的数字是1'.$postObj->FromUserName.'-'.$postObj->ToUserName;;
				break;
				case 2:
					$content = '您输入的数字是2';
				break;
				case 3:
					$content = '您输入的数字是3';
				break;
				case 4:
					$content = "<a href='http://www.imooc.com'>慕课</a>";
				break;
				case '英文':
					$content = 'imooc is ok';
				break;
				default :$content = '服务号正在维护升级,给你带来的不便敬请谅解,后续将会有更多功能上线,让我们拭目以待!';
			}	
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
				// $content  = '18723180099';
				$msgType  = 'text';
				echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
			
		}//if end
	}//reponseMsg end
	
}
?>
