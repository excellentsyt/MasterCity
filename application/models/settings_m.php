<?php

class Settings_m extends MY_Model {
    
    protected $_table_name = 'settings';
    protected $_order_by = 'id';
    
    public $rules_contact = array(
        'address' => array('field'=>'address', 'label'=>'lang:Address', 'rules'=>'trim'),
        'gps' => array('field'=>'gps', 'label'=>'lang:Gps', 'rules'=>'trim'),
        'email' => array('field'=>'email', 'label'=>'lang:ContactMail', 'rules'=>'trim'),
        'email_alert' => array('field'=>'email_alert', 'label'=>'lang:inputContactMailAlert', 'rules'=>'trim'),
        'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim'),
        'fax' => array('field'=>'fax', 'label'=>'lang:Fax', 'rules'=>'trim'),
        'address_footer' => array('field'=>'address_footer', 'label'=>'lang:Address Footer', 'rules'=>'trim'),
    );
    
    public $rules_template = array(
        'template' => array('field'=>'address', 'label'=>'lang:Template', 'rules'=>'trim'),
        'tracking' => array('field'=>'tracking', 'label'=>'lang:Tracking', 'rules'=>'trim'),
        'facebook' => array('field'=>'facebook', 'label'=>'lang:Facebook', 'rules'=>'trim'),
    );
    
    public $rules_system = array(
        'noreply' => array('field'=>'noreply', 'label'=>'lang:No-reply email', 'rules'=>'trim'),
        'zoom' => array('field'=>'zoom', 'label'=>'lang:Zoom index', 'rules'=>'trim'),
    );

    public function get_new()
	{
        $setting = new stdClass();
        $setting->field = '';
        $setting->value = '';
        
        return $setting;
	}
    
    public function get_fields()
    {
        $query = $this->db->get($this->_table_name);

        $data = array();
        foreach($query->result() as $key=>$setting)
        {
            $data[$setting->field] = $setting->value;
        }
        
        return $data;
    }
    
    public function save_settings($post_data)
    {
        $this->delete_fields($post_data);
        
        $data = array();
        foreach($post_data as $key=>$value)
        {
            $data[] = array(
               'field' => $key,
               'value' => $value
            );
        }
        
        $this->db->insert_batch($this->_table_name, $data); 
    }
    
    public function delete_fields($fields = array())
    {
        $this->db->where_in('field', array_keys($fields));
        $this->db->delete($this->_table_name);
    }
    
}



