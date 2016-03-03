<?php

class MY_Controller extends CI_Controller {
    
    public $data = array();
    
	public function __construct(){
        parent::__construct();
        
        $this->data['time_start'] = microtime(true);
        
        //$this->load->model('user');
        //$this->user->isloggedin();
        
        $this->data['errors'] = array();
        $this->data['site_name'] = config_item('site_name');
        

	}
}