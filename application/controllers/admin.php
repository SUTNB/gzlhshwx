<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin extends Admin_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('wxsendmsg');
        $this->load->library('user_model');
        $this->load->library('pagination');
    }
    //审核列表
    public function index(){
        $status = 1;
        $total_rows = $this->user_model->get_app_total_rows($status);
        $config['base_url'] = site_url('admin/index');
        $config['total_rows'] = $total_rows;//记录总数，这个没什么好说的了，就是你从数据库取得记录总数   
        $config['per_page'] = 10; //每页条数。额，这个也没什么好说的。。自己设定。默认为10好像。   
        $config['first_link'] = '首页'; // 第一页显示   
        $config['last_link'] = '末页'; // 最后一页显示   
        $config['next_link'] = '下一页 >'; // 下一页显示
        $config['prev_link'] = '< 上一页'; // 上一页显示   
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li><a style="color:white;background-color:blue">'; // 当前页开始样式   
        $config['cur_tag_close'] = '</a></li>'; 
        $config['num_links'] = 10;//    当前连接前后显示页码个数。意思就是说你当前页是第5页，那么你可以看到3、4、5、6、7页。   
        $config['uri_segment'] = 3; 
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
        $data['offset'] = $this->uri->segment(3);
        if($data['offset'] == null) $data['offset']=0;
        $data['apply'] = $this->user_model->get_appedmoney($status, $config['per_page'], $data['offset']);
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
    //已审核列表
    public function acceptlist(){
        $status = $this->uri->segment(3);
        //echo $status;die;
        $total_rows = $this->user_model->get_app_total_rows($status);
        $config['base_url'] = site_url('admin/acceptlist/'.$status);
        $config['total_rows'] = $total_rows;//记录总数，这个没什么好说的了，就是你从数据库取得记录总数   
        $config['per_page'] = 10; //每页条数。额，这个也没什么好说的。。自己设定。默认为10好像。   
        $config['first_link'] = '首页'; // 第一页显示   
        $config['last_link'] = '末页'; // 最后一页显示   
        $config['next_link'] = '下一页 >'; // 下一页显示
        $config['prev_link'] = '< 上一页'; // 上一页显示   
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li><a style="color:white;background-color:blue">'; // 当前页开始样式   
        $config['cur_tag_close'] = '</a></li>'; 
        $config['num_links'] = 10;//    当前连接前后显示页码个数。意思就是说你当前页是第5页，那么你可以看到3、4、5、6、7页。   
        $config['uri_segment'] = 4; 
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
        $data['offset'] = $this->uri->segment(4);
        if($data['offset'] == null) $data['offset']=0;
        $data['applyed'] = $this->user_model->get_appedmoney($status, $config['per_page'], $data['offset']);
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
        $data['info'] = array(2=>'发放成功', 3=>'已拒绝', 4=>'发放中', 5=>'发放失败!');
        $this->load->view('acceptlist.html',$data);
    }
    //用户列表
    public function userlist(){
        $total_rows = $this->user_model->get_user_total_rows();
        $config['base_url'] = site_url('admin/userlist');   
        $config['total_rows'] = $total_rows;//记录总数，这个没什么好说的了，就是你从数据库取得记录总数   
        $config['per_page'] = 10; //每页条数。额，这个也没什么好说的。。自己设定。默认为10好像。   
        $config['first_link'] = '首页'; // 第一页显示   
        $config['last_link'] = '末页'; // 最后一页显示   
        $config['next_link'] = '下一页 >'; // 下一页显示
        $config['prev_link'] = '< 上一页'; // 上一页显示   
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li><a style="color:white;background-color:blue">'; // 当前页开始样式   
        $config['cur_tag_close'] = '</a></li>'; 
        $config['num_links'] = 10;//    当前连接前后显示页码个数。意思就是说你当前页是第5页，那么你可以看到3、4、5、6、7页。   
        $config['uri_segment'] = 3; 
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
        $data['offset'] = $this->uri->segment(3);
        if($data['offset'] == null) $data['offset']=0;
        $data['user'] = $this->user_model->get_usermoney($config['per_page'], $data['offset']);
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
    //冻结账户列表
    public function freeze(){
        $total_rows = $this->user_model->get_userfreeze_total_rows();
        $config['base_url'] = site_url('admin/freeze');   
        $config['total_rows'] = $total_rows;//记录总数，这个没什么好说的了，就是你从数据库取得记录总数   
        $config['per_page'] = 10; //每页条数。额，这个也没什么好说的。。自己设定。默认为10好像。   
        $config['first_link'] = '首页'; // 第一页显示   
        $config['last_link'] = '末页'; // 最后一页显示   
        $config['next_link'] = '下一页 >'; // 下一页显示
        $config['prev_link'] = '< 上一页'; // 上一页显示   
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li><a style="color:white;background-color:blue">'; // 当前页开始样式   
        $config['cur_tag_close'] = '</a></li>'; 
        $config['num_links'] = 10;//    当前连接前后显示页码个数。意思就是说你当前页是第5页，那么你可以看到3、4、5、6、7页。   
        $config['uri_segment'] = 3; 
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
        $data['offset'] = $this->uri->segment(3);
        if($data['offset'] == null) $data['offset']=0;
        $data['user_freeze'] = $this->user_model->get_userfreeze($config['per_page'], $data['offset']);
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
    //系统设置页面
    public function system_set(){
        $this->load->view('system_set.html');
    }
     //菜单设置页面
    public function menu_set(){
        $this->load->view('menu_set.html');
    }
    //创建菜单
    public function create_menu(){
        $data = '
            {
     "button":[
     {	
          "type":"click",
          "name":"专属名片",
          "key":"V1001_EWM"
      },
      {
           "name":"求职招聘",
           "sub_button":[
           {	
               "type":"view",
               "name":"爆料有奖",
               "url":"http://m.gzl.ccoo.cn/bbs/?flag=5"
            },
            {
               "type":"view",
               "name":"有奖签到",
               "url":"http://mp.weixin.qq.com/s?__biz=MzAwOTAxMjM5OQ==&mid=400926492&idx=1&sn=f1d60d0703dc447c1ef1f4bb86cc014c#rd"
            },
            {
               "type":"view",
               "name":"求职招聘",
               "url":"http://m.gzl.ccoo.cn/post/job/?from=singlemessage&isappinstalled=0"
            }]
       },
       {
           "name":"领取红包",
           "sub_button":[
           {	
               "type":"click",
               "name":"领取红包",
               "key":"V1001_HBSM"
            },
            {
               "type":"view",
               "name":"掌上商城",
               "url":"https://wap.koudaitong.com/v2/showcase/feature?alias=ypzdpuei"
            },
            {
               "type":"click",
               "name":"提现",
               "key":"V1001_HB"
            }]
       }]
 }
                ';
        $result = $this->wxsendmsg->create_menu($data);
        echo $result;
    }
}
