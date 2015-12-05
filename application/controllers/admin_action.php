<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin_action extends Admin_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('wxhb');
        $this->load->library('wxsendmsg');
        $this->load->library('user_model');
    }
    //发放红包
    public function accept_do(){
        $note_id = $this->input->post("note_id");
        $openid = $this->input->post("openid");
        $money = $this->input->post("money");
        //echo $note_id.'---------'.$openid.'----------'.$money;die;
        $result = $this->user_model->set_app_status($note_id, 4);//4为发放中
        if($result ){
            $result_pay= $this->wxhb->pay($openid,$money);
            if($result_pay == 'SUCCESS'){
                 $this->user_model->set_app_status($note_id, 2);//2为发放中
                 $content = "感谢您参与本次活动,您还可继续邀请好友哦!";
                 $this->wxsendmsg->responseTextBycustom($openid, $content);
                 echo json_encode(array('code'=> 1, 'message' => '发放成功'));
            }else{
                 $this->user_model->set_app_status($note_id, 5);//5发放失败
                 echo json_encode(array('code'=> -1, 'message' => '发放失败'));
            }
        }
    }
    //拒绝发放红包
    public function refuse_do(){
        $note_id = $this->input->post("note_id");
        $openid = $this->input->post("openid");
        //echo $note_id.'---------'.$openid.'----------'.$money;die;
        $result = $this->user_model->set_app_status($note_id, 3);//3为拒绝提现
        if($result ){
                 $content = "您的提现要求已被拒绝,如有问题请联系客服!";
                 $this->wxsendmsg->responseTextBycustom($openid, $content);
                 echo json_encode(array('code'=> 1, 'message' => '操作成功'));
            }else{
                 echo json_encode(array('code'=> -1, 'message' => '操作失败'));
            }
    }
    
        //冻结账号
    public function freeze_do(){
        $openid = $this->input->post("openid");
        //echo $note_id.'---------'.$openid.'----------'.$money;die;
        $result = $this->user_model->update_user_status($openid, 2);//2为冻结账户
        if($result ){
                 $content = "您的账户已被冻结,如有问题请联系客服!";
                 $this->wxsendmsg->responseTextBycustom($openid, $content);
                 echo json_encode(array('code'=> 1, 'message' => '操作成功'));
            }else{
                 echo json_encode(array('code'=> -1, 'message' => '操作失败'));
            }
        }
        
         //解冻账号
    public function unfreeze_do(){
        $openid = $this->input->post("openid");
        //echo $note_id.'---------'.$openid.'----------'.$money;die;
        $user_info = $this->wxsendmsg->get_userinfo($openid);
        $status = 1;
        if($user_info['subscribe'] == 0)
        {
                $status = 3;//取消关注
         }
        $result = $this->user_model->update_user_status($openid, $status);//恢复正常或者取消关注
        if($result ){
                 $content = "您的账户已被解冻,给您带来的不便尽请谅解!";
                 $this->wxsendmsg->responseTextBycustom($openid, $content);
                 echo json_encode(array('code'=> 1, 'message' => '操作成功'));
            }else{
                 echo json_encode(array('code'=> -1, 'message' => '操作失败'));
            }
        }
    
}
