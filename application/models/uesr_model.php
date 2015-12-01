<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model{
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    /*
     * 检查用户是否关注过公众号
     */
    public function check($openid){
        $result = $this->db->where(array('opend'=>$openid))->get('user_money');
        if($result->num_rows() == 0)
             return true;
        else 
            return false;
    }
     //获取毫秒时间戳
   function get_time(){
        $time = explode ( " ", microtime () );  
        $time = $time [1] . ($time [0] * 1000);  
        $time2 = explode ( ".", $time );  
        return  $time2 [0];
   }
    /*
     * 初始化用户账户
     */
    public function set_user_money($object){
        $this->db->trans_start();
        $new_scene_id = $this->get_time();
        if(isset($object->EventKey)){
                $a = explode("_", $object->EventKey);
                $scene_id = $a[1];
                $sql2 = "UPDATE user_money SET popul_num= popul_num+1, money = money + 8 where scene_id=".$scene_id;
                $result2 = $this->db->query($sql2);        
        }
        $money = 88;
        $status = 1;
        $sql1 = "INSERT INTO user_money (openid, scene_id, money, status) VALUES (".$object->FormUserName.", ".$new_scene_id.",".$money.",".$status.")";
        $result1 = $this->db->query($sql1);
        $this->db->trans_complete();
        if($result1 && $result2){
            return true;
        }else{
            return false;
        }
    }
    /*
     * 修改用户账户状态
     */
    public function set_user_status($openid, $status){
        $sql = "UPDATE user_money SET status= ".$status." where openid=".$openid;
        $this->db->query($sql);
        return $this->db->affected_rows();
    }
    /*
     * 提现申请
     */
    public function app_money($openid, $money){
        //$sql1 = "INSERT INTO user_money (openid, scene_id, money, status) VALUES (".$object->FormUserName.", ".$new_scene_id.",".$money.",".$status.")";
    }
}

