<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Hello extends CI_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('wxsendmsg');
        //$this->load->model('user_model');
        //$this->wxsendmsg->getWxAccessToken();die;
    }
    public function index(){          
		$nonce     = $_GET['nonce'];
		$token      = TOKEN;
		$timestamp = $_GET['timestamp'];
		$echostr   = $_GET['echostr'];
		$signature = $_GET['signature'];
		//形成数组，然后按字典序排序
		$array = array();
		$array = array($nonce, $timestamp, $token);
		sort($array);
		//拼接成字符串,sha1加密 ，然后与signature进行校验
		$str = sha1( implode( $array ) );
		if( $str  == $signature && $echostr ){
			//第一次接入weixin api接口的时候
			echo  $echostr;
			exit;
		}else{
			$this->reponseMsg();
		}
	}
	// 接收事件推送并回复
	public function reponseMsg(){
		//1.获取到微信推送过来post数据（xml格式）
		$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
		//2.处理消息类型，并设置回复类型和内容
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
                                                                      $result_user = $this->set_user_money($postObj);
                                                                      if($result_user['code'] == -1){
                                                                          $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $result_user['message']);
                                                                          return;
                                                                      }
                                                                      $content1 = "欢迎关注我们的微信公众平台";
                                                                      $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $content1);
                                                                      if(isset($result_user['fopenid'])){
                                                                          $content2 = "有一位好友通过你的二维码关注了我们的微信平台,奖励你0.08元";
                                                                          $this->wxsendmsg->responseTextBycustom($result_user['fopenid'], $content2);
                                                                      }
			}
                        if( strtolower($postObj->Event == 'unsubscribe') ){
                                    $this->load->library('user_model');
                                    $result_user = $this->set_user_money($postObj);
                                     if($result_user['code'] == -1){
                                        return;
                                      }
                                    $this->user_model->update_user_status($postObj->FromUserName, 3);//设置用户状态为取消关注
                        }
	}
        else if(strtolower($postObj->MsgType) == 'text' && trim($postObj->Content)=='领取红包'){
            $this->load->library('user_model');
            $result = $this->applpay($postObj);//检查账户是否符合要求
            if($result['code'] == 1){
                $money  = floor($result['money']/100) *100;//只取整数
                $result_act = $this->user_model->app_money($postObj->FromUserName, $money);
                if($result_act)
                    $this->wxsendmsg->responseText($postObj, $result_act);
            }
        }
        else if(strtolower($postObj->MsgType) == 'text' && trim($postObj->Content)=='生成二维码'){
//                                                                    $this->load->library('wxhb');
//				$result = $this->wxhb->pay($postObj->FromUserName,100);
                                                                    //$this->wxsendmsg->responseText($postObj,$result);
                                                                    $result_user = $this->set_user_money($postObj);
                                                                      if($result_user['code'] == -1){
                                                                          $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $result_user['message']);
                                                                          return;
                                                                      }
                                                                      $content = "二维码正在生成中........请稍后";
                                                                      $result1 = $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $content);//客服接口发送文本消息
                                                                      if($result1['errcode'] == 0){
                                                                                $result2 = $this->create_send_poster($postObj);//生成并发送二维码
                                                                      }else {
                                                                                $error = "系统错误!请稍后再试!";
                                                                                $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $error);//客服接口发送文本消息
                                                                     }
				//$result = '服务号正在维护升级,给你带来的不便敬请谅解,后续将会有更多功能上线,让我们拭目以待!';
		}else{
			switch( trim($postObj->Content) ){
				case 1:
					$content = '您输入的数字是1';
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
                                        $this->wxsendmsg->responseText($postObj, $content);	
		}//if end
	}//reponseMsg end
                //设置用户账号
                /*
                 * 如果该用户未曾关注公众号则正常初始化用户账户,生成$scene_id,初始化金额.
                 * 如果该用户关注过公众号(表中已有该用户且账户状态为取消关注),则变更账户状态为正常,金额不变.
                 */
                public function set_user_money($object){
                    //return array('code'=> -5, 'message'=>"测试");
                    $this->load->library('user_model');
                    //return array('code'=> -5, 'message'=>"测试");
                    $result = $this->user_model->check($object->FromUserName);//检查用户是否未曾关注公众号
                    if($result['code'] == 1){
                        $data = $this->user_model->set_user_money($object);
                    }else if($result['code'] ==  2){
                        $data = $this->user_model->update_user_status($object->FromUserName, 1);
                    }elseif($result['code'] ==  3){
                        $data = array('code'=> 8, 'message'=>$result['message']);
                    }else{
                        $data = array('code'=> -1, 'message'=>'您的账号存在违规操作,已被冻结.如有问题请联系客服');
                    }
                    return $data;
                }     
                  //申请提现
	public function applpay($postObj){
                             $this->load->library('user_model');
                             $result = $this->user_model->check($postObj->FromUserName);//检查用户类型
                             if($result['code'] == 1){
                                 $content = "您尚未参与我们的活动!";
                                 $this->wxsendmsg->responseText($postObj, $content);
                                 return false;
                             }elseif($result['code'] == -1){
                                 $content = "您的账号存在违规操作,已被冻结.如有问题请联系客服";
                                 $this->wxsendmsg->responseText($postObj, $content);
                                 return false;
                             }elseif($result['code'] == 3){//用户账户正常.开始检查金额是否不足,是否已经提交申请,是否已被拒绝
                                 $result_apply = $this->user_model->check_money($postObj->FromUserName);
                                 if($result_apply['code'] != 1){
                                     $this->wxsendmsg->responseText($postObj, $result_apply['message']);
                                     return false;
                                 }else{//操作数据库
                                     return $result_apply;
                                 }
                             }
	}
        public function sec2str($sec){
            return sprintf("%02d:%02d:%02d",$sec/3600,$sec%3600/60,$sec%60);
        }

        //生成海报
                public function create_send_poster($postObj){
                    //$this->wxsendmsg->upload_qrcode();
                    $this->load->library('user_model');
                    $data = $this->user_model->get_userinfo($postObj->FromUserName);//$postObj->FromUserName
                    if($data['time'] === \NULL ||time() - strtotime($data['time']) > 7*24*60*60){  
                        $save_result = $this->user_model->set_userinfo($postObj->FromUserName, '123465','13246');
                        $result_create = $this->wxsendmsg->create_qrcode($data['scene_id']);
                        if($result_create['code'] == 1){
                            $result_upload = $this->wxsendmsg->upload_qrcode($result_create['url']); 
                            if($result_upload['code'] == 1){
                                //return array('code'=>-3,'message'=>'debug');
                                $save_result = $this->user_model->set_userinfo($postObj->FromUserName, $result_create['url'], $result_upload['media_id']);
                                //return array('code'=>-3,'message'=>'debug');
                                //return array('code'=>-2,'message'=>$save_result['message']);
                                if($save_result['code'] == 1){
                                    $this->wxsendmsg->send_qrcode($postObj, $result_upload['media_id']);//发送二维码
                                }else{
                                    return array('code'=>-3,'message'=>$save_result['message']);
                                }
                            }else{
                                return array('code'=>-2,'message'=>$result_upload['message']);
                            }
                        }else{
                            return array('code'=>-1,'message'=>$result_create['message']);
                        }
                    }else if (time() - strtotime($data['time']) < 3*24*60*60) {
                        $this->wxsendmsg->send_qrcode($postObj, $data['media_id']);//发送二维码
                 }else if (time() - strtotime($data['time']) < 7*24*60*60 && time() - strtotime($data['time']) > 3*24*60*60) {
                       $result_upload = $this->wxsendmsg->upload_qrcode($data['qrcode_url']);
                            if($result_upload['code'] == 1){
                                $save_result = $this->user_model->update_userinfo($postObj->FromUserName, $result_upload['media_id']);
                                if($save_result['code'] == 1){
                                    $this->wxsendmsg->send_qrcode($postObj, $result_upload['media_id']);//发送二维码
                                }else{
                                    return array('code'=>-3,'message'=>$save_result['message']);
                                }
                           }
                        }
                }
}

