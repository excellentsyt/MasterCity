<?php

class Property extends Frontend_Controller
{

	public function __construct ()
	{
		parent::__construct();
	}
    
    public function _remap($method)
    {
        $this->index();
    }
    
    private function _get_purpose()
    {
        if(isset($this->data['is_purpose_sale'][0]['count']))
        {
            return lang('Sale');
        }
        
        if(isset($this->data['is_purpose_rent'][0]['count']))
        {
            return lang('Rent');
        }
        
        return lang('Sale');
    }

    public function index()
    {
        $lang_code = (string) $this->uri->segment(3);
        $property_id = (string) $this->uri->segment(2);
        $property_slug = (string) $this->uri->segment(4);
        
        $lang_id = $this->data['lang_id'];
        
        $option_sum = '';
        
        /* Fetch estate data */
        
        $this->data['property_id'] = $property_id;
        
        /* Fetch options names */
        $this->data['options'] = $this->option_m->get_options($lang_id);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);
        $option_categories = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_name'] = $row->option;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_type'] = $row->type;
            $this->data['category_options_'.$row->parent_id][$row->option_id]['option_suffix'] = $row->suffix;
            
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_checkbox'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_dropdown'] = array();
            $this->data['category_options_'.$row->parent_id][$row->option_id]['is_text'] = array();
            
            $option_categories[$row->option_id] = $row->parent_id;
        }
        /* End fetch options names */
        
        /* Fetch estate data */
        $estate_data = $this->estate_m->get_array($property_id, TRUE);
        
        if($estate_data['is_activated'] != 1)
        {
            //show_error(lang_check('Propertyactivated'));
            redirect('');
        }
        
        foreach($estate_data as $key=>$val)
        {
            $this->data['estate_data_'.$key] = $val;
        }
        
        if(isset($this->data['options'][$estate_data['id']]))
        foreach($this->data['options'][$estate_data['id']] as $key=>$val)
        {
            if($val != '')
            {
                $this->data['estate_data_option_'.$key] = $val;
                
                // Set Category data
                if(isset($option_categories[$key]))
                {
                    $this->data['category_options_'.$option_categories[$key]][$key]['option_value'] = $val;
    
                    if($this->data['category_options_'.$option_categories[$key]][$key]['option_type'] == 'CHECKBOX')
                    {
                        //if($val == 'true') // if you want to show just available amenities in template
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_checkbox'][] = array('true'=>'true');
                    }
                    elseif($this->data['category_options_'.$option_categories[$key]][$key]['option_type'] == 'DROPDOWN')
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_dropdown'][] = array('true'=>'true');
                    }
                    else
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['is_text'][] = array('true'=>'true');
                    }
                    
                    $this->data['category_options_'.$option_categories[$key]][$key]['option_id'] = $key;
                    
                    /* icon */
                    $this->data['category_options_'.$option_categories[$key]][$key]['icon']='';
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/icons/option_id/'.$key.'.png'))
                    {
                        $this->data['category_options_'.$option_categories[$key]][$key]['icon']=
                        '<img src="assets/img/icons/option_id/'.$key.'.png" alt="'.$val.'"/>';
                    }
                }
            }
        }
        
        if(!isset($this->data['estate_data_option_10']))$this->data['estate_data_option_10'] = '';
        $this->data['estate_data_printurl'] = 
            site_url('frontend/property_print/'.$estate_data['id'].'/'.$lang_code.'/'.url_title_cro($this->data['estate_data_option_10']));
        
        $this->data['estate_data_icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
        if(isset($this->data['estate_data_option_6']))
        {
            if($this->data['estate_data_option_6'] != '' && $this->data['estate_data_option_6'] != 'empty')
            {
                if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                               '/assets/img/markers/'.$this->data['color_path'].$this->data['estate_data_option_6'].'.png'))
                $this->data['estate_data_icon'] = 'assets/img/markers/'.$this->data['color_path'].$this->data['estate_data_option_6'].'.png';
                
                if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].'selected/'.$this->data['estate_data_option_6'].'.png'))
                $this->data['estate_data_icon'] = 'assets/img/markers/'.$this->data['color_path'].'selected/'.$this->data['estate_data_option_6'].'.png';
            }
        }
        
        /* End Fetch estate data */ 
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['page_images'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);
            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($estate_data['repository_id'] == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
        }
        
        // Has attributes
        $this->data['has_page_images'] = array();
        if(count($this->data['page_images']))
            $this->data['has_page_images'][] = array('count'=>count($this->data['page_images']));
        
        /* Fetch estate data end */
        
        if(!empty($property_id)){
            if(!isset($this->data['options'][$property_id][10]))$this->data['options'][$property_id][10] = '-';
            if(!isset($this->data['options'][$property_id][17]))$this->data['options'][$property_id][17] = '-';
            if(!isset($this->data['options'][$property_id][8]))$this->data['options'][$property_id][8] = '-';
            
            $this->data['page_navigation_title'] = $this->data['options'][$property_id][10];
            $this->data['page_title'] = $this->data['options'][$property_id][10];
            $this->data['page_body']  = $this->data['options'][$property_id][17];
            $this->data['page_description'] = character_limiter(strip_tags($this->data['options'][$property_id][8]), 160);
            
            $this->data['estate_image_url'] = 'assets/img/no_image.jpg';
            if(isset($this->data['page_images'][0]))
            {
                $this->data['estate_image_url'] = $this->data['page_images'][0]->url;
            }
        }
        else
        {
            show_404(current_url());
        }
        
        /* Fetch agent */
        
        $agent = $this->user_m->get_agent($this->data['property_id']);
        
        if(count($agent))
        {
            $this->data['agent_name_surname'] = $agent['name_surname'];
            $this->data['agent_phone'] = $agent['phone'];
            $this->data['agent_mail'] = $agent['mail'];
            $this->data['agent_address'] = $agent['address'];
        }
        
        $this->data['has_agent'] = array();
        if(count($agent))
            $this->data['has_agent'][] = array('count'=>count($agent));
        
        // Thumbnail
        if(count($agent) && isset($this->data['images_'.$agent['repository_id']]))
        {
            $this->data['agent_image_url'] = $this->data['images_'.$agent['repository_id']][0]->thumbnail_url;
        }
        else
        {
            $this->data['agent_image_url'] = 'assets/img/user-agent.png';
        }
        
        // Get agent estates
        
        $agent_estates_list = array();
        if(isset($agent['id']))
            $agent_estates_list = $this->user_m->get_estates($agent['id']);
        
        /* End fetch agent */
        
        /* Get all estates data */
        $estates = $this->estate_m->get_by(array('is_activated' => 1));
        $options = $this->option_m->get_options($this->data['lang_id']);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        $this->data['all_estates'] = array();
        $this->data['agent_estates'] = array();
        foreach($estates as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = $estate_obj->address;
            $estate['date'] = $estate_obj->date;
            $estate['is_featured'] = $estate_obj->is_featured;
                        
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_obj->id][$row2->option_id]))
                {
                    $row1 = $options[$estate_obj->id][$row2->option_id];
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    
                    if(!empty($row1))
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                }
            }
            
            
            $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'marker_blue.png';
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].$estate['option_6'].'.png';
                }
            }
            
            if($this->data['property_id'] == $estate['id']){
            if(isset($estate['option_6']))
            {
                if($estate['option_6'] != '' && $estate['option_6'] != 'empty')
                {
                    if(file_exists(FCPATH.'templates/'.$this->data['settings_template'].
                                   '/assets/img/markers/'.$this->data['color_path'].'selected/'.$estate['option_6'].'.png'))
                    $estate['icon'] = 'assets/img/markers/'.$this->data['color_path'].'selected/'.$estate['option_6'].'.png';
                }
            }
            }
            
            // Url to preview
            if(isset($options[$estate_obj->id][10]))
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_obj->id][10]));
            }
            else
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_obj->repository_id]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_obj->repository_id][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['all_estates'][] = $estate;
            
            if(in_array($estate_obj->id, $agent_estates_list))
                $this->data['agent_estates'][] = $estate;
        }
        
        $this->data['all_estates_center'] = calculateCenter($estates);
        
        /* End get all estates data */


        
        // Get slideshow
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['slideshow_images'] = array();
        $num=0;
        foreach($files as $key=>$file)
        {
            if($estate_data['repository_id'] == $file->repository_id)
            {
                $slideshow_image = array();
                $slideshow_image['num'] = $num;
                $slideshow_image['url'] = base_url('files/'.$file->filename);
                $slideshow_image['first_active'] = '';
                if($num==0)$slideshow_image['first_active'] = 'active';
                
                $this->data['slideshow_images'][] = $slideshow_image;
                $num++;
            }
        }
        // End Get slideshow
        
        /* Helpers */
        $this->data['year'] = date('Y');
        /* End helpers */
        
        /* Widgets functions */
        $this->data['print_menu'] = get_menu($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_lang_menu'] = get_lang_menu($this->language_m->get_array_by(array('is_frontend'=>1)), $this->data['lang_code']);
        $this->data['page_template'] = $this->temp_data['page']->template;
        /* End widget functions */

        /* Validation for contact */
        $rules = array(
            'firstname' => array('field'=>'firstname', 'label'=>'lang:FirstLast', 'rules'=>'trim|required|xss_clean'),
            'email' => array('field'=>'email', 'label'=>'lang:Email', 'rules'=>'trim|required|valid_email|xss_clean'),
            'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|required|xss_clean'),
            'address' => array('field'=>'address', 'label'=>'lang:Address', 'rules'=>'trim|required|xss_clean'),
            'message' => array('field'=>'message', 'label'=>'lang:Message', 'rules'=>'trim|required|xss_clean'),
            'fromdate' => array('field'=>'fromdate', 'label'=>'lang:FromDate', 'rules'=>'trim|xss_clean'),
            'todate' => array('field'=>'todate', 'label'=>'lang:ToDate', 'rules'=>'trim|xss_clean')
        );
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            $data_t = $this->page_m->array_from_post(array('firstname', 'email', 'phone', 'message', 'address', 'fromdate', 'todate'));
            
            // Save enquire to database
            $this->load->model('enquire_m');
            $data = array();
            $data['name_surname'] = $data_t['firstname'];
            $data['phone'] = $data_t['phone'];
            $data['mail'] = $data_t['email'];
            $data['message'] = $data_t['message'];
            $data['address'] = $data_t['address'];
            $data['fromdate'] = $data_t['fromdate'];
            $data['todate'] = $data_t['todate'];
            $data['readed'] = 0;
            $data['property_id'] = $this->data['property_id'];
            $data['date'] = date('Y-m-d H:i:s');
            $this->enquire_m->save($data);
            
            $this->session->set_flashdata('email_sent', 'email_sent_true');
            
            // Send email
            $this->load->library('email');
            $to_mail = '';
            
            if(count($agent))$to_mail = $agent['mail'];
            
            if(empty($to_mail))$to_mail = $this->data['settings_email'];
            
            $this->email->from($this->data['settings_noreply'], 'Web page');
            $this->email->to($to_mail);
            
            $this->email->subject(lang_check('Message from real-estate web'));
            
            $message='';
            foreach($data as $key=>$value){
            	$message.="$key:\n$value\n";
            }
            
            $this->email->message($message);
            if ( ! $this->email->send())
            {
                $this->session->set_flashdata('email_sent', 'email_sent_false');
            }
            else
            {
                $this->session->set_flashdata('email_sent', 'email_sent_true');
            }

            redirect($this->uri->uri_string());
        }
        
        $this->data['validation_errors'] = validation_errors();
        
        $this->data['form_sent_message'] = '';
        if($this->session->flashdata('email_sent'))
        {
            if($this->session->flashdata('email_sent') == 'email_sent_true')
            {
                $this->data['form_sent_message'] = '<p class="alert alert-success">'.lang_check('message_sent_successfully').'</p>';
            }
            else
            {
                $this->data['form_sent_message'] = '<p class="alert alert-error">'.lang_check('message_sent_error').'</p>';
            }  
        }
        
        // Form errors
        $this->data['form_error_firstname'] = form_error('firstname')==''?'':'error';
        $this->data['form_error_email'] = form_error('email')==''?'':'error';
        $this->data['form_error_phone'] = form_error('phone')==''?'':'error';
        $this->data['form_error_message'] = form_error('message')==''?'':'error';
        $this->data['form_error_address'] = form_error('address')==''?'':'error';
        $this->data['form_error_fromdate'] = form_error('fromdate')==''?'':'error';
        $this->data['form_error_todate'] = form_error('todate')==''?'':'error';
        
        // Form values
        $this->data['form_value_firstname'] = set_value('firstname', '');
        $this->data['form_value_email'] = set_value('email', '');
        $this->data['form_value_phone'] = set_value('phone', '');
        $this->data['form_value_message'] = set_value('message', '');
        $this->data['form_value_address'] = set_value('address', '');
        $this->data['form_value_fromdate'] = set_value('fromdate', '');
        $this->data['form_value_todate'] = set_value('todate', '');
        
        /* End validation for contact */
        
        /* Fetch options data */
        $options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        $this->data['options_name'] = array();
        $this->data['options_suffix'] = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
            $this->data['options_values_'.$row->option_id] = '';
            $this->data['options_values_li_'.$row->option_id] = '';
            
            if(count(explode(',', $row->values)) > 0)
            {
                $options = '<option value="">'.$row->option.'</option>';
                $options_li = '';
                foreach(explode(',', $row->values) as $key2 => $val)
                {
                    $options.='<option value="'.$val.'">'.$val.'</option>';
                    
                    $active = '';
                    if($this->_get_purpose() == $val)$active = 'active';
                    $options_li.= '<li class="'.$active.' cat_'.$key2.'"><a href="#">'.$val.'</a></li>';
                }
                $this->data['options_values_'.$row->option_id] = $options;
                $this->data['options_values_li_'.$row->option_id] = $options_li;
            }
        }
        
        /* Define purpose */
        $this->data['is_purpose_rent'] = array();
        $this->data['is_purpose_sale'] = array();
        $this->data['is_purpose_sale'][] = array('count'=>'1');
        
        if(isset($this->data['estate_data_option_4'])){
            if(stripos($this->data['estate_data_option_4'], lang_check('Rent')) !== FALSE)
            {
                $this->data['is_purpose_rent'][] = array('count'=>'1');
            }
            if(stripos($this->data['estate_data_option_4'], lang_check('Sale')) !== FALSE)
            {
                $this->data['is_purpose_sale'][] = array('count'=>'1');
            }
        }
        /* End define purpose */
        
        /* {MOULE_ADS} */
        $this->load->model('ads_m');
        $this->data['ads'] = array();
        
        foreach($this->ads_m->ads_types as $type_key=>$type_name)
        {
            $ads_by_type = $this->ads_m->get_by(array('type'=>$type_key));
            
            $num_ads = count($ads_by_type);

            $this->data['has_ads_'.$type_name] = array();
            if($num_ads > 0)
            {
                $rand_ad_key = rand(0, $num_ads-1);
                
                if(isset($ads_by_type[$rand_ad_key]))
                {
                    $rand_image = rand(0, count($this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id])-1);
                    
                    $this->data['random_ads_'.$type_name.'_link'] = $ads_by_type[$rand_ad_key]->link;
                    $this->data['random_ads_'.$type_name.'_repository'] = $ads_by_type[$rand_ad_key]->repository_id;
                    $this->data['random_ads_'.$type_name.'_image'] = $this->data['images_'.$ads_by_type[$rand_ad_key]->repository_id][$rand_image]->url;
                    $this->data['has_ads_'.$type_name][] = array('count' => $num_ads);
                }
            }
        }
        /* {/MOULE_ADS} */

        // Get templates
        $templatesDirectory = opendir(FCPATH.'templates/'.$this->data['settings_template'].'/components');
        // get each template
        $template_prefix = 'page_';
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != ".." && strpos($tempFile, '.php') !== FALSE) {
                if(substr_count($tempFile, $template_prefix) == 0)
                {
                    $template_output = $this->parser->parse($this->data['settings_template'].'/components/'.$tempFile, $this->data, TRUE);
                    //$template_output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $template_output);
                    $this->data['template_'.substr($tempFile, 0, -4)] = $template_output;
                }
            }
        }
        
        $output = $this->parser->parse($this->data['settings_template'].'/property.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }

}