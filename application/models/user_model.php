<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model{
     function __construct() {
        parent::__construct();
        $this->load->database();
    }
    /*
     * 检查用户是否关注过公众号
     */
    public function check($openid){
        return true;
        $this->load->database();
        $result = $this->db->where(array('openid'=>$openid))->get('user_money');
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
            return "true";
        }else{
            return "flase";
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
     * 查询金额是否满足提现
     */
    public function check_money($openid, $money){
        $sql1 = "SELECT money FROM user_money where  openid=".$openid;
        $query1 = $this->db->query($sql1);
        $row1 = $query1->row();
        if($row1->money >= $money){
                 $sql2 = "SELECT * FROM apply_getmoney where  openid=".$openid."and (status = 1 or status = 4)";
                 $query2 = $this->db->query($sql2);
                 if($result->num_rows() == 0)
                     return array('code'=>1, 'message' =>'可以提现');
                 else
                     return array('code'=>-2,'message'=>'已提交提现申请,请耐心等待');
        }else{
                return array('code'=>-1,'message'=>'金额不足');
            }
    }
    /*
     * 提现申请
     */
    public function app_money($openid, $money){
        $this->db->trans_start();
        $note_id = md5(time());
        $format = 'DATE_W3C';
        $sub_time = standard_date($format, time());
        $status = 1;
        $sql1 = "INSERT INTO apply_getmoney (note_id, openid, money, status) VALUES (".$note_id.", ".$openid.",".$money.",".$sub_time.",".$status.")";
        $sql2 = "UPDATE user_money SET money= money-".$money." where openid=".$openid;
        $result1 = $this->db->query($sql1);
        $result2 = $this->db->query($sql2);
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
    public function set_app_status($note_id, $status){
        $sql = "UPDATE user_money SET status= ".$status." where openid=".$note_id;
        $this->db->query($sql);
        return $this->db->affected_rows();
    }
}

