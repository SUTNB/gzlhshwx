<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin_action extends CI_Controller{
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
    
}
