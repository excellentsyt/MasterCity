<?php

class Frontend_Controller extends MY_Controller 
{
	public function __construct(){
		parent::__construct();
        
        if(config_item('installed') == false)
        {
            redirect('configurator');
            exit();
        }
        
        $this->data['listing_uri'] = config_item('listing_uri');
        if(empty($this->data['listing_uri']))$this->data['listing_uri'] = 'property';
        
        /* Load Helpers */
        $this->load->helper('text');    
        
        /* Load libraries */
        $this->load->library('parser');
        
        $this->load->library('form_validation');
        
        $this->load->library('session');
        $this->load->library('pagination');
        
        /* Load models */
        $this->load->model('language_m');
        $this->load->model('page_m');
        $this->load->model('file_m');
        $this->load->model('user_m');
        $this->load->model('repository_m');
        $this->load->model('estate_m');
        $this->load->model('option_m');
        $this->load->model('settings_m');
        $this->load->model('slideshow_m');
        
        $this->form_validation->set_error_delimiters('<p class="alert alert-error">', '</p>');
        
        $CI =& get_instance();
        $CI->form_languages = $this->language_m->get_form_dropdown('language', FALSE, FALSE);
        
        // Fetch settings
        $this->load->model('settings_m');
        foreach($this->settings_m->get_fields() as $key=>$value)
        {
            if($key == 'address')
            {
                $value = str_replace('"', '\\"', $value);
            }
            
            $this->data['settings_'.$key] = $value;
            
            $this->data['has_settings_'.$key] = array();
            if(!empty($value))
            {
                $this->data['has_settings_'.$key][] = array('count'=>'1');
            }
        }
        
        // Extra JS features enabled
        $this->data['has_extra_js'] = array();
        if($this->uri->segment(2) == 'editproperty')
            $this->data['has_extra_js'][] = array('count'=>'1');
        
        // Get page data
        $this->data['lang_code'] = (string) $this->uri->segment(1);
        $this->data['page_id'] = (string) $this->uri->segment(2);
        $this->data['page_slug'] = (string) $this->uri->segment(3);
        $this->data['pagination_offset'] = 0;
        
        // If frontend
        if($this->data['page_id'] == 'typeahead')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = (string) $this->uri->segment(4);
            $this->data['pagination_offset'] = (string) $this->uri->segment(5);
        }
        else if($this->data['page_id'] == 'ajax')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = (string) $this->uri->segment(4);
            $this->data['pagination_offset'] = (string) $this->uri->segment(5);
        }
        else if($this->data['page_id'] == 'showroom' || $this->data['lang_code'] == 'showroom')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == 'expert' || $this->data['lang_code'] == 'expert')
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == $this->data['listing_uri'] || $this->data['lang_code'] == $this->data['listing_uri'])
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }
        else if($this->data['page_id'] == 'login' || 
                $this->data['page_id'] == 'myproperties' ||
                $this->data['page_id'] == 'editproperty' ||
                $this->data['page_id'] == 'deleteproperty' ||
                $this->data['page_id'] == 'logout' ||
                $this->data['page_id'] == 'listproperty' )
        {
            $this->data['page_slug'] = '';
            $this->data['lang_code'] = (string) $this->uri->segment(3);
            $this->data['page_id'] = '';
        }

        if(empty($this->data['page_id']))
        {
            // Get first menu item page
            $first_page = $this->page_m->get_first();
            
            if(!empty($first_page))
                $this->data['page_id'] = $first_page->id;
        }
        else if(!is_numeric($this->data['page_id']))
        {
            $this->data['page_id'] = $this->page_m->get_id_by_name ($this->data['page_id']);
        }
        
        if(empty($this->data['lang_code']))
        {
            $this->data['lang_code'] = $this->language_m->get_default();
        }
        
        $this->data['lang_id'] = $this->language_m->get_id($this->data['lang_code']);
        
        if(empty($this->data['lang_id']))
            show_404(current_url());

        $this->data['page_current_url'] = site_url($this->uri->uri_string());
        
        // Check if is it RTL
        $this->data['is_rtl'] = array();
        $lang_data = $this->language_m->get($this->data['lang_id']);
        $rtl_test = $this->input->get('test', TRUE);
        if($lang_data->is_rtl == 1 || $rtl_test == 'rtl')
        {
            $this->data['is_rtl'][]= array('count'=>'1');
        }
        
        // Fetch menu
        $this->temp_data['menu'] = $this->page_m->get_nested($this->data['lang_id']);

        // Fetch current page
        $this->temp_data['page'] = $this->page_m->get_lang($this->data['page_id']);
               
        if(!empty($this->temp_data['page']) && !empty($this->data['page_id'])){
            $this->data['page_navigation_title'] = $this->temp_data['page']->{'navigation_title_'.$this->data['lang_id']};
            $this->data['page_title'] = $this->temp_data['page']->{'title_'.$this->data['lang_id']};
            $this->data['page_body']  = $this->temp_data['page']->{'body_'.$this->data['lang_id']};
            $this->data['page_description']  = character_limiter(strip_tags($this->temp_data['page']->{'description_'.$this->data['lang_id']}), 160);
            $this->data['page_keywords']  = $this->temp_data['page']->{'keywords_'.$this->data['lang_id']};
        }
        else
        {
            if (!is_resource($CI->db->conn_id) && !is_object($CI->db->conn_id))
                show_error('Database conenction failed');

            show_404(current_url());
        }
        
        // URL-s
        $this->data['ajax_load_url'] = site_url('frontend/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id']);
        $this->data['ajax_showroom_load_url'] = site_url('showroom/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$this->input->get('cat', TRUE));
        $this->data['ajax_expert_load_url'] = site_url('expert/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$this->input->get('cat', TRUE));
        $this->data['typeahead_url'] = site_url('frontend/typeahead/'.$this->data['lang_code'].'/'.$this->data['page_id']);
        
        // Load custom translations
        $this->config->set_item('language', $this->language_m->get_name($this->data['lang_code']));
        //$this->lang->load('frontend_base');
        
        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].'/language/'.$this->language_m->get_name($this->data['lang_code'])))
        {
            $this->lang->load('frontend_template', '', FALSE, TRUE, FCPATH.'templates/'.$this->data['settings_template'].'/');
        }
        else
        {
            $this->config->set_item('language', 'english');
            $this->lang->load('frontend_template', '', FALSE, TRUE, FCPATH.'templates/'.$this->data['settings_template'].'/');
            //$this->config->set_item('language', $this->language_m->get_name($this->data['lang_code']));
        }
        
        if(!file_exists(APPPATH.'language/'.$this->language_m->get_name($this->data['lang_code']).'/form_validation_lang.php'))
        {
            $this->config->set_item('language', 'english');
        }
        
        // Define language for template
        $lang = $this->lang->get_array();
        foreach($lang as $key=>$row)
        {
            $this->data['lang_'.$key] = $row;
        }
        
        // Color definition for demo purposes
        $this->data['color'] = '';
        $this->data['color_path'] = '';
        $this->data['has_color'] = array();
        $this->data['has_color_picker'] = array();
        $color = $this->input->get('color', TRUE);
        if(empty($color))
        {
            $color = $this->session->userdata('color');
        }
        if($this->config->item('color') !== FALSE)
        {
            $color = $this->config->item('color');
        }
        if($this->config->item('color_picker') !== FALSE)
        {
            if($this->config->item('color_picker') == TRUE)
            {
                $this->data['has_color_picker'][] = array('selected_color'=>$color);
            }
        }
        
        if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].'/assets/css/styles_'.$color.'.css') &&
           file_exists(FCPATH.'templates/'.$this->data['settings_template'].'/assets/img/markers/'.$color))
        {
            $this->data['color'] = $color;
            $this->data['color_path'] = $color.'/';
            $this->data['has_color'][] = array('color'=>$color);
            $this->session->set_userdata('color', $color);
        }
        
        // homepage_url
        $this->data['homepage_url'] = site_url('');
        $this->data['homepage_url_lang'] = site_url($this->data['lang_code']);
        
        /* Check login */
        $this->data['is_logged_user'] = array();
        $this->data['is_logged_other'] = array();
        $this->data['not_logged'][] = array('count'=>'1');
        if($this->user_m->loggedin() == TRUE)
        {
            if($this->session->userdata('type') == 'USER')
            {
                $this->data['is_logged_user'][] = array('count'=>'1');
                $this->data['not_logged'] = array();
            }
            else
            {
                $this->data['is_logged_other'][] = array('count'=>'1');
                $this->data['not_logged'] = array();
            }
        }
        
        $this->data['logout_url'] = site_url('frontend/logout/'.$this->data['lang_code']);
        $this->data['login_url'] = site_url('admin/dashboard');
        $this->data['front_login_url'] = site_url('frontend/login/'.$this->data['lang_code']);
        $this->data['myproperties_url'] = site_url('frontend/myproperties/'.$this->data['lang_code']);
        $this->data['search_query'] = $this->input->get('search');
        
		// Load stuff
        //$this->load->model('page_m');
        
		// Fetch navigation
		//$this->data['menu'] = $this->page_m->get_nested();
        //$this->data['news_archive_link'] = $this->page_m->get_archive_link();
		//$this->data['meta_title'] = config_item('site_name');
	}
}