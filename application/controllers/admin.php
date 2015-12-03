<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin extends CI_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->library('wxsendmsg');
        $this->load->library('wxhb');
    }
    public function index(){
        $this->load->view('judge.html');
    }
}
