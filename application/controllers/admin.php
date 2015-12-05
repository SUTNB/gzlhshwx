<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin extends Admin_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('wxsendmsg');
        $this->load->library('user_model');
    }
    public function index(){
        $data['apply'] = $this->user_model->get_appmoney();
        foreach($data['apply']  as &$v):
            $user_info = $this->wxsendmsg->get_user_byopenid($v['openid']);
            if($user_info['subscribe'] == 0)
            {
                $v['yn'] = 3;
                continue;
            }
            $v['yn'] = 1;
            $v['headimgurl'] = $user_info['headimgurl'];
            $v['nickname'] = $user_info['nickname'];
            $v['area'] = $user_info['country'].'-'.$user_info['province'].'-'.$user_info['city'];
        endforeach;
        //print_r($data);die;
        $this->load->view('judge.html', $data);
    }
    public function acceptlist(){
        $data['applyed'] = $this->user_model->get_appedmoney();
        foreach($data['applyed']  as &$v):
            $user_info = $this->wxsendmsg->get_user_byopenid($v['openid']);
             if($user_info['subscribe'] == 0)
            {
                $v['yn'] = 3;
                continue;
            }
            $v['yn'] = 1;
            $v['headimgurl'] = $user_info['headimgurl'];
            $v['nickname'] = $user_info['nickname'];
            $v['area'] = $user_info['country'].'-'.$user_info['province'].'-'.$user_info['city'];
        endforeach;
        $data['status'] = array(2=>'btn btn-success', 3=>'btn btn-danger', 4=>'btn btn-info', 5=>'btn btn-warning');
        $data['info'] = array(2=>'发放成功', 3=>'已拒绝', 4=>'发放中', 5=>'发放失败,快联系牛犇!');
        $this->load->view('acceptlist.html',$data);
    }
    public function userlist(){
        $data['user'] = $this->user_model->get_usermoney();
        //print_r($data);die;
        foreach($data['user']  as &$v):
            if($v['status'] == 3)
            {
                $v['yn'] = 3;
                continue;
            }
            $v['yn'] = 1;
            $user_info = $this->wxsendmsg->get_user_byopenid($v['openid']);
            $v['headimgurl'] = $user_info['headimgurl'];
            $v['nickname'] = $user_info['nickname'];
            $v['area'] = $user_info['country'].'-'.$user_info['province'].'-'.$user_info['city'];
        endforeach;
//        echo "<pre>";
//        print_r($data);
//        echo "<pre>";
//        die;
        $this->load->view('userlist.html', $data);
    }
    public function freeze(){
        $data['user_freeze'] = $this->user_model->get_userfreeze();
        //print_r($data);die;
        foreach($data['user_freeze']  as &$v):
           $user_info = $this->wxsendmsg->get_user_byopenid($v['openid']);
             if($user_info['subscribe'] == 0)
            {
                $v['yn'] = 3;
                continue;
            }
            $v['yn'] = 1;
            $v['headimgurl'] = $user_info['headimgurl'];
            $v['nickname'] = $user_info['nickname'];
            $v['area'] = $user_info['country'].'-'.$user_info['province'].'-'.$user_info['city'];
        endforeach;
//        echo "<pre>";
//        print_r($data);
//        echo "<pre>";
//        die;
        $this->load->view('freeze.html',$data);
    }
}
