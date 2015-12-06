<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model{
   /*
    * 检查用户是否关注过公众号
   */
 public function check($openid){
                   $query = "SELECT status, money FROM user_money where openid='".$openid."' limit 1";
                   $result = mysql_query($query);
                   $a = mysql_fetch_assoc($result);
                   if(!isset($a['status'])){    
                       return array('code' => 1, 'message' => '新用户');
                   } else {
                       if($a['status'] == 2){
                           return array('code' => -1, 'message' => '账号已冻结');
                       }
                       elseif($a['status'] == 3){
                           return array('code' => 2, 'message' => '取消关注');
                       }
                       else {
                           return array('code' => 3, 'message' => '正常', 'money' => $a['money']);
                       }
                   }
         }
     //获取毫秒时间戳
   function get_time(){
//       $s = "1449121600000";
//        $time = explode ( " ", microtime () );  
//        $time = $time [1] . ($time [0] * 1000);  
//        $time2 = explode ( ".", $time );  
        return  time();
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
                $sql2 = "UPDATE user_money SET popul_num= popul_num+1, money = money + 24 where scene_id=".$scene_id;
                $result2 = mysql_query($sql2);        
        }
        $money = 24;
        $status = 1;
        $sql1 = "INSERT INTO user_money (openid, scene_id, money, status) VALUES ('".$object->FromUserName."', '".$new_scene_id."',".$money.",".$status.")";
        $result1 = mysql_query($sql1);
        if($result2){
            if(isset($object->EventKey)){
                 $query = "SELECT openid, money FROM user_money where scene_id= '".$scene_id."' limit 1";
                 $result = mysql_query($query);
                 $f = mysql_fetch_assoc($result);
                return array('code'=>1, 'message'=>'设置成功','fopenid' => $f['openid'], 'money' => $f['money']);
            }
            if(!$result1){
                return array('code'=>-4, 'message'=>'服务器忙碌,请稍后再试!');//用户过多,生成插入有失败,提示稍后重试
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
    public function check_money($openid){
        $sql1 = "SELECT count(*) FROM apply_getmoney where  openid='".$openid."'and (status = 1 or status = 4)";
        $result1 = mysql_query($sql1);
        $query1 = mysql_fetch_assoc($result1);
        if($query1['count(*)'] == 0){
                $sql2 = "SELECT count(*) FROM apply_getmoney where  openid='".$openid."'and status = 3";
                $result2 = mysql_query($sql2);
                $query2 = mysql_fetch_assoc($result2);
                if($query2['count(*)'] == 0){
                        $sql3 = "SELECT money FROM user_money where  openid='".$openid."' limit 1";
                        $result3 = mysql_query($sql3);
                        $query3 = mysql_fetch_assoc($result3);
                         if($query3['money'] >= 100){
                                return array('code'=>1, 'message' =>'可以提现','money'=>$query3['money']);
                        }else{
                                return array('code'=>-1,'message'=>'金额不足,您的账户余额为: '.($query3['money']/100)."满一元才可提现");
                        }		
                }else{
                        return array('code'=>-3, 'message' =>'提现已被拒绝');
                }
        }else{
                return array('code'=>-2,'message'=>'已提交提现申请,请耐心等待');
            }
    }
    /*
     * 提现申请
     */
    public function app_money($openid, $money){
        $note_id = md5(time());
        $status = 1;//等待审核
        $sql1 = "UPDATE user_money SET money= money-".$money." where openid='".$openid."'";
        $result1 = mysql_query($sql1);
        $sql2 = "INSERT INTO apply_getmoney (note_id, openid, money, sub_time, status) VALUES ('".$note_id."', '".$openid."',".$money.", NOW(),'".$status."')";
        if($result1)
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
        $sql = "UPDATE apply_getmoney SET status= ".$status." where note_id='".$note_id."'";
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
    /*
     * 获取申请信息
     */
    public function get_appmoney(){
        $sql = "SELECT apply_id, openid,note_id, money, sub_time FROM apply_getmoney where status = 1";
        $result = mysql_query($sql);
        $data = array();
        while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$data[] = $row;
        }
        return $data;
    }
    /*
     * 获取已审核过的列表
     */
    public function get_appedmoney(){
        $sql = "SELECT apply_id, openid,note_id, money, sub_time, status FROM apply_getmoney where status != 1";
        $result = mysql_query($sql);
        $data = array();
        while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$data[] = $row;
        }
        return $data;
    }
    /*
     * 获取所有用户列表
     */
    public function get_usermoney(){
        $sql = "SELECT * FROM user_money where status != 2 order by user_rank";
        $result = mysql_query($sql);
        $data = array();
        while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$data[] = $row;
        }
        return $data;
    }
    /*
     * 获取所有冻结用户get_userfreeze
     */
    public function get_userfreeze(){
        $sql = "SELECT * FROM user_money where status = 2";
        $result = mysql_query($sql);
        $data = array();
        while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$data[] = $row;
        }
        return $data;
    }
}

