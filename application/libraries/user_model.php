<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model{
   /*
    * 检查用户是否关注过公众号
   */
 public function check($openid){
                   $query = "SELECT status FROM user_money where openid='".$openid."'";
                   $result = mysql_query($query);
                   $a = mysql_fetch_assoc($result);
                   if(!isset($a['status'])){    
                       return array('code' => 1, 'message' => '新用户');
                   } else {
                       if($a['status'] == 2)
                           return array('code' => -1, 'message' => '账号已冻结');
                       elseif($a['status'] == 3)
                           return array('code' => 2, 'message' => '取消关注');
                       else {
                           return array('code' => 3, 'message' => '正常');
                       }
                   }
         }
     //获取毫秒时间戳
   function get_time(){
       $s = "1449121600000";
        $time = explode ( " ", microtime () );  
        $time = $time [1] . ($time [0] * 1000);  
        $time2 = explode ( ".", $time );  
        return  $time2 [0] - $s;
   }
    /*
     * 初始化用户账户
     */
    public function set_user_money($object){
        $new_scene_id = $this->get_time();
        $result2 = true;
        if(isset($object->EventKey)){
                $a = explode("_", $object->EventKey);
                $scene_id = $a[1];
                $sql2 = "UPDATE user_money SET popul_num= popul_num+1, money = money + 8 where scene_id=".$scene_id;
                $result2 = mysql_query($sql2);        
        }
        $money = 88;
        $status = 1;
        $sql1 = "INSERT INTO user_money (openid, scene_id, money, status) VALUES ('".$object->FromUserName."', '".$new_scene_id."',".$money.",".$status.")";
        $result1 = mysql_query($sql1);
        if($result1 && $result2){
            if(isset($object->EventKey)){
                 $query = "SELECT openid FROM user_money where scene_id= '".$scene_id."' limit 1";
                 $result = mysql_query($query);
                 $openid = mysql_fetch_assoc($result);
                return array('code'=>1, 'message'=>'设置成功','fopenid' => $openid['openid']);
            }
            return array('code'=>1, 'message'=>'设置成功');
        }else{
            return array('code'=>-3, 'message'=>'设置失败');
        }
    }
    /*
     * 修改用户账户状态
     */
    public function update_user_status($openid, $status){
        $sql = "UPDATE user_money SET status= '".$status."' where openid='".$openid."'";
        $result = mysql_query($sql);
        if($result){
            return array('code'=>1, 'message'=>'修改成功');
        }else{
            return array('code'=>-2, 'message'=>'修改失败');
        }
    }
    /*
     * 查询金额是否满足提现
     */
    public function check_money($openid, $money){
        $sql1 = "SELECT money FROM user_money where  openid='".$openid."'";
        $query1 = mysql_query($sql1);
        $row1 = mysql_fetch_assoc($query1);
        if($row1['money'] >= $money){
                 $sql2 = "SELECT count(*) FROM apply_getmoney where  openid='".$openid."'and (status = 1 or status = 4)";
                 $result = mysql_query($sql2);
                 $query2 = mysql_fetch_assoc($result);
                 if($query2['count(*)'] == 0){
                     return array('code'=>1, 'message' =>'可以提现');
                 } else{
                     return array('code'=>-2,'message'=>'已提交提现申请,请耐心等待');
                 }
        }else{
                return array('code'=>-1,'message'=>'金额不足');
            }
    }
    /*
     * 提现申请
     */
    public function app_money($openid, $money){
        $note_id = md5(time());
        $format = 'DATE_W3C';
        $sub_time = standard_date($format, time());
        $status = 1;
        $sql1 = "INSERT INTO apply_getmoney (note_id, openid, money, status) VALUES (".$note_id.", '".$openid."',".$money.",".$sub_time.",".$status.")";
        $sql2 = "UPDATE user_money SET money= money-".$money." where openid='".$openid."'";
        $result1 = mysql_query($sql1);
        $result2 = mysql_query($sql2);
        if($result1 && $result2){
            return true;
        }else{
            return false;
        }
    }
        /*
     * 修改提现状态
     */
    public function set_app_status($note_id, $status){
        $sql = "UPDATE user_money SET status= ".$status." where note_id=".$note_id;
        $result = mysql_query($sql);
        if($result){
            return true;
        }
        else{
            return false;
        }
    }
    /*
     * 获得用户信息
     */
    public function get_userinfo($openid){
                   $query = "SELECT * FROM user_money where openid='".$openid."'";
                   $result = mysql_query($query);
                   return mysql_fetch_assoc($result);
    }
    /*
     * 初始化用户信息
     */
    public function set_userinfo($openid, $url, $media_id){
        $sql = "UPDATE user_money SET qrcode_url= '".$url."',time=NOW()".",media_id='".$media_id."' where openid='".$openid."'";
        $result = mysql_query($sql);
        //return array('code'=>-2, 'message'=>'debug');
        if($result){
            return array('code'=>1, 'message'=>'设置成功');
        }else{
            return array('code'=>-2, 'message'=>'设置失败');
        }
    }
    /*
     * 设置用户信息
     */
    public function update_userinfo($openid, $media_id){
        $sql = "UPDATE user_money SET media_id='".$media_id."' where openid='".$openid."'";
        $result = mysql_query($sql);
        if($result){
            return array('code'=>1, 'message'=>'设置成功');
        }else{
            return array('code'=>-2, 'message'=>'设置失败');
        }
    }
}

