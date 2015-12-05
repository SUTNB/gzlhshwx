<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login extends CI_Controller{
    public function __construct() {
        parent::__construct();
    }
    public function login_page(){
        $this->load->view('login_page.html');
    }
    public function login_do(){
        if(!isset($_SESSION)){
            	session_start();	
        }
        $username = $this->input->post('username',TRUE);
        $password = $this->input->post('password',TRUE);
        if($username == 'gzlhsh' && $password == 'waxmqq008877'){
            $newdata = array (
	'username' => $username
            );
	$this->session->set_userdata($newdata);
	echo true;
        }else{
            echo false;
        }
    }
}

