<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Hello extends CI_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('wxsendmsg');
        //$this->load->model('user_model');
        //$this->wxsendmsg->getWxAccessToken();die;
    }
    public function index(){ 
                                   $echostr = "";
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
		if( $str  == $signature && !empty($echostr) ){
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
                                                                      $user = $this->wxsendmsg->get_userinfo($postObj->FromUserName);//获取用户信息
                                                                      if(!isset($user['city']) || $user['city'] != '四平'){//不是四平的不算粉丝
                                                                            $content = "公主岭骆驼蓄电池\n为您送上0.19元红包\n邀请好友一起参与,\n每邀请一位公主岭的好友\n即可获得0.19元红包\n满1元即可提现\n赶快点击下面专属名片参加活动吧！\n(本活动仅限公主岭地区人参与)\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";                                                                                                  
                                                                            $this->wxsendmsg->responseText($postObj, $content);
                                                                           return;
                                                                      }
                                                                      $result_user = $this->set_user_money($postObj);
                                                                      if($result_user['code'] == -1){
                                                                          $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $result_user['message']);
                                                                          return;
                                                                      }
                                                                      if(isset($result_user['fopenid'])){//提示关注双方
                                                                          $user_info_s = $this->wxsendmsg->get_user_byopenid($postObj->FromUserName);
                                                                          $result_user['money'] = $result_user['money']/100;
                                                                          $content2 = "您的好友  【".$user_info_s['nickname']."】  通过你的二维码关注了我们的微信平台\n【您的余额: ".$result_user['money']."元】\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                                                          $this->wxsendmsg->responseTextBycustom($result_user['fopenid'], $content2);
                                                                          $user_info_f = $this->wxsendmsg->get_user_byopenid($result_user['fopenid']);
                                                                          $content1 = "【".$user_info_s['nickname']."】您好，您的好友　【".$user_info_f['nickname']."】 邀你一起抢红包,【".$user_info_f['nickname']."】,您获得了0.19元，已打入您的账户！点击专属名片，和小伙伴们一起领红包吧！\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                                                      }else{
                                                                          if(isset($result_user['warn'])){
                                                                             $result = $this->user_model->get_money($result_user['warn']);
                                                                              $content1 = "欢迎回来，【您的余额为：".($result['money']/100)."元】，快去邀请你的好友，来一起抢红包吧！\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                                                          }else{
                                                                                $content1 = "公主岭骆驼蓄电池\n为您送上0.19元红包\n邀请好友一起参与,\n每邀请一位公主岭的好友\n即可获得0.19元红包\n满1元即可提现\n赶快点击下面专属名片参加活动吧！\n(本活动仅限公主岭地区人参与)\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";                                                                                                  
                                                                          }
                                                                     }
                                                                      $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $content1);
                                                                      return;
			}
                        if( strtolower($postObj->Event == 'unsubscribe') ){
                                    $this->load->library('user_model');
                                    $result_user = $this->set_user_money($postObj);
                                     if($result_user['code'] == -1){
                                        return;
                                      }
                                    $this->user_model->update_user_status($postObj->FromUserName, 3);//设置用户状态为取消关注
                                    return;
                        }
                        if( strtolower($postObj->Event == 'SCAN') ){//已关注事件
                                    $this->load->library('user_model');
                                    $result_user = $this->set_user_money($postObj);
                                     if($result_user['code'] == -1){
                                        $content3 = $result_user['message'];
                                      }else{
                                          $content3 = "您已关注我们的公众平台,可点击生成专属二维码,好友通过您的二维码关注我们的平台后,您将获得0.19元的奖励, 满一元可以提现\n【您的余额为:".($result_user['money']/100)."元】\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                      }
                                      $this->wxsendmsg->responseText($postObj, $content3);
                                      return;
                                    //$this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $content3);
                        }
                        if( strtolower($postObj->Event == 'CLICK') && $postObj->EventKey == 'V1001_EWM'){//二维码生成
                                  $result_user = $this->set_user_money($postObj);
                                   if($result_user['code'] == -1){
                                   $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $result_user['message']);
                                            return;
                                    }
                                   if($result_user['code'] == -4){//插入失败,键重复,提示稍后重试
                                            $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $result_user['message']);
                                            return;
                                   }
                                            $content = "广告一下\n马上就来......\n么么哒！\n\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                            $result1 = $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $content);//客服接口发送文本消息
                                            if($result1['errcode'] == 0){
                                                          $result2 = $this->create_send_poster($postObj);//生成并发送二维码
                                            }else {
                                                          $error = "系统错误!请稍后再试!";
                                                          $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $error);//客服接口发送文本消息
                                           }
                                           return;
                        }
                        if( strtolower($postObj->Event == 'CLICK') && $postObj->EventKey == 'V1001_HB'){//提现
                                $this->load->library('user_model');
                                $result = $this->applpay($postObj);//检查账户是否符合要求
                                //$result = "系统忙，请稍后再试";
                                //$this->wxsendmsg->responseText($postObj, $result);
                                if($result['code'] == 1){
                                            $money  = floor($result['money']/100) *100;//只取整数
                                            $surplus =  $result['money'] - $money;//计算余额
                                            $result_act = $this->user_model->app_money($postObj->FromUserName, $money);
                                            if($result_act){
                                                $user_money_info = "恭喜你提现申请已经提交\n由于体现用户巨大，请耐心等待...\n正常工作时间最晚1小时内到账，\n您的余额为:【".($surplus/100)."元】\n咱岭城人民就是这么讲究,领了红包还要打个电话感谢一下！\n\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                                $this->wxsendmsg->responseText($postObj, $user_money_info);
                                            }
                                }
                                return;
                        }
                        if( strtolower($postObj->Event == 'CLICK') && $postObj->EventKey == 'V1001_HBSM'){//红包说明
                                  $hb_info = "公主岭骆驼蓄电池\n为您送上0.19元红包\n邀请好友一起参与,\n每邀请一位公主岭的好友\n即可获得0.19元红包\n满1元即可提现\n赶快点击下面专属名片参加活动吧！\n(本活动仅限公主岭地区人参与)\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                                  $this->wxsendmsg->responseText($postObj, $hb_info);
                        }
                        return;
	}
        else if(strtolower($postObj->MsgType) == 'text' && trim($postObj->Content)=='领取红包'){
            $this->load->library('user_model');
            $result = $this->applpay($postObj);//检查账户是否符合要求
            if($result['code'] == 1){
                $money  = floor($result['money']/100) *100;//只取整数
                $surplus =  $result['money'] - $money;//计算余额
                $result_act = $this->user_model->app_money($postObj->FromUserName, $money);
                if($result_act){
                    $user_money_info = "恭喜你提现申请已经提交\n由于体现用户巨大，请耐心等待...\n正常工作时间最晚1小时内到账，\n您的余额为:【".($surplus/100)."元】\n咱岭城人民就是这么讲究,领了红包还要打个电话感谢一下！\n\n======赞助商广告====\n公主岭通达汽车服务中心\n骆驼蓄电池四平地区总代理\n朝阳轮胎，嘉实多机油 \n地址：公主岭市胜利路68号(响铃宾馆后)\n电话：0434-6226885\n            5066885\n客服微信:\n   zn15714447788\n            ↓↓↓\n            ↓↓↓\n点击这里领取50元\n==================";
                    $this->wxsendmsg->responseText($postObj, $user_money_info);
                }
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
                                                                      if($result_user['code'] == -4){//插入失败,键重复,提示稍后重试
                                                                          $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $result_user['message']);
                                                                          return;
                                                                      }
                                                                      $content = "二维码正在生成中........请稍后";
                                                                      $result1 = $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $content);//客服接口发送文本消息
                                                                      //errmsg
                                                                      $this->wxsendmsg->responseText($postObj, $result1['errmsg']);
//                                                                      if($result1['errcode'] == 0){
//                                                                                $result2 = $this->create_send_poster($postObj);//生成并发送二维码
//                                                                      }else {
//                                                                                $error = "系统错误!请稍后再试!";
//                                                                                $this->wxsendmsg->responseTextBycustom($postObj->FromUserName, $error);//客服接口发送文本消息
//                                                                     }
				//$result = '服务号正在维护升级,给你带来的不便敬请谅解,后续将会有更多功能上线,让我们拭目以待!';
		}else{
			$content = "还没参加活动的赶快参加哦！\n点击专属名片，推广领红包\n本活动真实有效\n平台承接商业推广\n微信公众平台运营托管\n微网站，企业网站建设\n微信公众平台二次开发\n联系客服微信:hsh0434\n注:本活动只针对微信地址为公主岭的用户";
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
                        $data = array('code'=> 8, 'message'=>$result['message'], 'money'=>$result['money']);
                    }else{
                        $data = array('code'=> -1, 'message'=>'您的账号存在违规操作,已被冻结.如有问题请联系客服');
                    }
                    return $data;
                }     
                  //申请提现
	public function applpay($postObj){
                             $this->load->library('user_model');
                             $result = $this->user_model->check($postObj->FromUserName);//检查用户类型
                             //return $result['message'];
                             if($result['code'] == 1 || $result['code'] == 2){
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
                        //$save_result = $this->user_model->set_userinfo($postObj->FromUserName, '123465','13246');
                        $result_create = $this->wxsendmsg->create_qrcode($data['scene_id'], $postObj->FromUserName);
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

