<?php

class Slideshow_m extends MY_Model {
    
    protected $_table_name = 'slideshow';
    protected $_order_by = 'id';
    public $rules = array();

    public function get_new()
	{
        $page = new stdClass();
        $page->repository_id = NULL;
        $page->date = date('Y-m-d H:i:s');
        
        return $page;
	}

}


