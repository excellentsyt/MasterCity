<?php

class Frontend extends Frontend_Controller
{

	public function __construct ()
	{
		parent::__construct();
	}
    
    private function _get_purpose()
    {
        if(isset($this->select_tab_by_title))
        if($this->select_tab_by_title != '')
        {
            return $this->select_tab_by_title;
        }
        
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
    
    private function check_login()
    {        
        $this->load->library('session');
        $this->load->model('user_m');
        
        // Login check
        if($this->user_m->loggedin() == FALSE)
        {
            redirect('frontend/login/'.$this->data['lang_code']);
        }
        else
        {
    	    $dashboard = 'admin/dashboard';
            
            if($this->session->userdata('type') == 'USER')
            {
                // LOGIN USER, OK
            }
            else
            {
                redirect($dashboard);
            }
        }
    }
    
    private function load_head_data()
    {
        /* Helpers */
        $this->data['year'] = date('Y');
        /* End helpers */
        
        /* Widgets functions */
        $this->data['print_menu'] = get_menu($this->temp_data['menu'], false, $this->data['lang_code']);
        $this->data['print_lang_menu'] = get_lang_menu($this->language_m->get_array_by(array('is_frontend'=>1)), $this->data['lang_code']);
        /* End widget functions */
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        $this->data['page_files'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
                $this->data['documents_'.$file->repository_id][] = $file;
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_documents'][] = $file;
                }
            }
            
            $this->data['files_'.$file->repository_id][] = $file;

            if($this->temp_data['page']->repository_id == $file->repository_id)
            {
                $this->data['page_files'][] = $file;
            }
        }
        
        /* Get all estates data */
        $estates = $this->estate_m->get_by(array('is_activated' => 1));
        $options = $this->option_m->get_options($this->data['lang_id']);
        
        $this->data['all_estates'] = array();
        foreach($estates as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = $estate_obj->address;
            $estate['date'] = $estate_obj->date;
            $estate['is_featured'] = $estate_obj->is_featured;
            
            // All estate options
            if(isset($options[$estate_obj->id]))
            foreach($options[$estate_obj->id] as $key1=>$row1)
            {                
                $estate['option_'.$key1] = $row1;
                $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
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

            $this->data['all_estates'][] = $estate;
        }
        
        $this->data['all_estates_center'] = calculateCenter($estates);
        
        /* End get all estates data */
        
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
    }
    
    
    public function myproperties()
    {
        $this->check_login();
        $this->load_head_data();
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myproperties');
        $this->data['page_title'] = lang_check('Myproperties');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['content_language_id'] = $this->data['lang_id'];
        
	    // Fetch all estates
        $this->data['estates'] = $this->estate_m->get_join();
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'USER'));
        
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

        $output = $this->parser->parse($this->data['settings_template'].'/myproperties.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function listproperty()
    {
        $this->check_login();
        $this->load_head_data();
        
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/myproperties.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function deleteproperty()
    {
        $this->check_login();
        
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang_check('Data editing disabled in demo'));
            redirect('frontend/myproperties/'.$this->data['lang_code']);
            exit();
        }
        
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['estate'] = $this->estate_m->get_dynamic_array($id);
            
            if(count($this->data['estate']) > 0)
            {
                //Check if user have permissions
                if($this->data['estate']['agent'] == $this->session->userdata('id'))
                {
                    $this->estate_m->delete($id);
                }
            }
        }           

        redirect('frontend/myproperties/'.$this->data['lang_code']);
    }
    
    public function editproperty()
    {
        $this->check_login();
        $this->load_head_data();

        $this->data['content_language_id'] = $this->data['lang_id'];
        $id = NULL;
        if($this->uri->segment(4) != '')
        {
            $id = $this->uri->segment(4);
        }
        
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['estate'] = $this->estate_m->get_dynamic_array($id);
            
            if(count($this->data['estate']) == 0)
            {
                $this->data['errors'][] = 'Estate could not be found';
                redirect('frontend/myproperties/'.$this->data['lang_code'], 'refresh');
            }
            
            //Check if user have permissions
            if($this->data['estate']['agent'] == $this->session->userdata('id'))
            {
            
            }
            else
            {
                redirect('frontend/myproperties/'.$this->data['lang_code'], 'refresh');
                exit();                
            }
            
            //var_dump($this->data['estate']);
            
            // Fetch file repository
            $repository_id = $this->data['estate']['repository_id'];
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>'estate_m'));
                
                // Update page with new repository_id
                $this->estate_m->save(array('repository_id'=>$repository_id), $this->data['estate']['id']);
            }
        }
        else
        {
            // Load estate data
            $this->data['estate'] = $this->estate_m->get_new_array();
        }
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Editproperty');
        $this->data['page_title'] = lang_check('Editproperty');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';

		// Pages for dropdown
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        
        // Get available agents
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'AGENT'));
        
        // Get all options
        foreach($this->option_m->languages as $key=>$val){
            $this->data['options_lang'][$key] = $this->option_m->get_lang(NULL, FALSE, $key);
        }
        $this->data['options'] = $this->option_m->get_lang_array(NULL, FALSE, $this->data['content_language_id']);
        
        $options_data = array();
        foreach($this->option_m->get() as $key=>$val)
        {
            $options_data[$val->id][$val->type] = 'true';
        }
        
        // Add rules for dynamic options
        $rules_dynamic = array();
        foreach($this->option_m->languages as $key_lang=>$val_lang){
            foreach($this->data['options'] as $key_option=>$val_option){
                $rules_dynamic['option'.$val_option['id'].'_'.$key_lang] = 
                    array('field'=>'option'.$val_option['id'].'_'.$key_lang, 'label'=>$val_option['option'], 'rules'=>'trim');
                //if($id == NULL)$this->data['estate']->{'option'.$val_option->id.'_'.$key_lang} = '';
                if(!isset($this->data['estate']))$this->data['estate']->{'option'.$val_option['id'].'_'.$key_lang} = '';
            }
        }
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->zoom_enabled = false;
            $file->download_url = base_url('files/'.$file->filename);
            $file->delete_url = site_url_q('files/upload/rep_'.$file->repository_id, '_method=DELETE&amp;file='.rawurlencode($file->filename));

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $file->zoom_enabled = true;
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
            }
            
            $this->data['files'][$file->repository_id][] = $file;
        }
        
        // Set up the form
        $rules = $this->estate_m->rules;
        $rules['date']['rules'] = 'trim';
        
        $this->form_validation->set_rules(array_merge($rules, $rules_dynamic));

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('frontend/editproperty/'.$this->data['lang_code'].'/'.$id);
                exit();
            }
            
            $data = $this->estate_m->array_from_post(array('gps', 'date', 'address', 'is_featured'));
            $dynamic_data = $this->estate_m->array_from_post(array_keys($rules_dynamic));
            
            if(empty($data['date']))
                $data['date'] = date('Y-m-d H:i:s');
                
            $data['is_activated'] = 0;
            $data['search_values'] = $data['address'];
            foreach($dynamic_data as $key=>$val)
            {
                $pos = strpos($key, '_');
                $option_id = substr($key, 6, $pos-6);
                
                if(!isset($options_data[$option_id]['TEXTAREA'])){
                    $data['search_values'].=' '.$val;
                }
                
                // TODO: test check, values for each language for selected checkbox
                if(isset($options_data[$option_id]['CHECKBOX'])){
                    if($options_data[$option_id]['CHECKBOX'] == 'true')
                    {
                        foreach($this->option_m->languages as $key_lang=>$val_lang){
                            foreach($this->data['options'] as $key_option=>$val_option){
                                if($val_option['id'] == $option_id)
                                {
                                    $data['search_values'].=' '.$val_option['option'];
                                }
                            }
                        }
                    }
                }
            }

            $insert_id = $this->estate_m->save($data, $id);
            
            if($this->session->userdata('type') != 'ADMIN')
            {
                $data['agent'] = $this->session->userdata('id');
            }
            else
            {
                $data['agent'] = $this->input->post('agent');
            }
            
            // Save dynamic options
            
            $dynamic_data['agent'] = $data['agent'];
            
            $this->estate_m->save_dynamic($dynamic_data, $insert_id);
            
            if(isset($this->data['settings_email_alert']))
            if($data['is_activated'] == 0 && $this->data['settings_email_alert'] == 1)
            {
                // Send email alert to contact address
                $this->load->library('email');
                
                $this->email->from($this->data['settings_noreply'], lang_check('Web page not-activated property'));
                $this->email->to($this->data['settings_email']);
                $this->email->subject(lang_check('Web page not-activated property'));
                
                $message='';
                $message.=lang_check('New not-activated property from user').": \n";
                $message.=$this->session->userdata('username')."\n\n";
                $message.=lang_check('Property edit link')." ($insert_id): \n";
                $message.=site_url('admin/estate/edit/'.$insert_id)."\n\n";
                
                $this->email->message($message);
                if ( ! $this->email->send())
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_false');
                }
                else
                {
                    $this->session->set_flashdata('email_sent', 'email_sent_true');
                }
                
            }
            
            $this->session->set_flashdata('message', 
                    '<p class="alert alert-success">'.lang_check('Changes saved').'</p>');
            
            redirect('frontend/editproperty/'.$this->data['lang_code'].'/'.$insert_id);
        }
        
        
        
        
        
        
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/editproperty.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    public function logout()
    {
        $this->user_m->logout();
        redirect($this->data['lang_code'], 'refresh');
    }
    
    public function login()
    {
        if($this->user_m->loggedin() == TRUE)
        {
    	    $dashboard = 'admin/dashboard';
            
            if($this->session->userdata('type') == 'USER')
            {
                redirect('frontend/myproperties/'.$this->data['lang_code']);
            }
            else
            {
                redirect($dashboard);
            }
        }
        
        $this->load_head_data();
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Login');
        $this->data['page_title'] = lang_check('Login');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';
        
        $this->data['is_registration'] = false;
        $this->data['is_login'] = false;

        // Set up the form for register
        if(isset($_POST['password_confirm']))
        {
            $this->data['is_registration'] = true;
            
            
            $rules = $this->user_m->rules_admin;
            $rules['name_surname']['label'] = 'lang:FirstLast';
            $rules['password']['rules'] .= '|required';
            $rules['type']['rules'] = 'trim';
            $rules['language']['rules'] = 'trim';
            $rules['mail']['label'] = 'lang:Email';
            $rules['mail']['rules'] .= '|valid_email|is_unique[user.mail]';
            
            $this->form_validation->set_rules($rules);
    
            // Process the form
            if($this->form_validation->run() == TRUE)
            {
                if($this->config->item('app_type') == 'demo')
                {
                    $this->session->set_flashdata('error_registration', 
                            lang_check('Data editing disabled in demo'));
                    redirect('frontend/login/'.$this->data['lang_code']);
                    exit();
                }
                
                $data = $this->user_m->array_from_post(array('name_surname', 'mail', 'password', 'username',
                                                             'address', 'description', 'mail', 'phone', 'type', 'language', 'activated'));
                if($data['password'] == '')
                {
                    unset($data['password']);
                }
                else
                {
                    $data['password'] = $this->user_m->hash($data['password']);
                }
                
                $data['type'] = 'USER';
                $data['activated'] = '1';
                $data['description'] = '';
                $data['language'] = '';
                $data['registration_date'] = date('Y-m-d H:i:s');
                
                $this->user_m->save($data, NULL);
                
                $this->session->set_flashdata('error_registration', 
                        lang_check('Thanks on registration, you can login now'));
                redirect('frontend/login/'.$this->data['lang_code'], 'refresh');
            }
        }
        else
        {
            $this->data['is_login'] = true;
            
    	    $dashboard = 'admin/dashboard';
                       
            // Set form
            $rules = $this->user_m->rules;
            $this->form_validation->set_rules($rules);
            
            // Process form
            if($this->form_validation->run() == TRUE)
            {
                // We can login and redirect
                if($this->user_m->login() == TRUE)
                {
                    redirect('frontend/myproperties/'.$this->data['lang_code']);
                }
                else
                {
                    $this->session->set_flashdata('error', 
                            lang_check('That email/password combination does not exists'));
                    redirect('frontend/login/'.$this->data['lang_code']);                
                }
            }
        }
        
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/login.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
    private function _custom_search_filtering(&$res_array, $options, $post_option)
    {
        foreach($res_array as $key=>$row)
        {
            foreach($post_option as $key1=>$val1)
            {
                if(is_numeric($val1) && $key1 != 'smart')
                {
                    $option_num = $key1;

                    if(strrpos($option_num, 'from') > 0)
                    {
                        $option_num = substr($option_num,0,-5);
                        
                        // For rentable
                        if($option_num == 36 && isset($this->data['is_purpose_rent'][0]['count']))
                            $option_num++;
                        
                        if(!isset($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] < $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                    else if(strrpos($option_num, 'to') > 0)
                    {
                        $option_num = substr($option_num,0,-3);
                        
                        // For rentable
                        if($option_num == 36 && isset($this->data['is_purpose_rent'][0]['count']))
                            $option_num++;
                        
                        if(!isset($options[$row['id']][$option_num]) || empty($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] > $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                    else
                    {
                        if(!isset($options[$row['id']][$option_num]))
                        {
                            unset($res_array[$key]);
                        }
                        else if($options[$row['id']][$option_num] != $val1)
                        {
                            unset($res_array[$key]);
                        }
                    }
                }
            }
        }
    }
    
    public function typeahead ()
    {
        $q = $this->input->post('q');
        $limit = $this->input->post('limit');
        $option_id = (string) $this->uri->segment(5);
        $option_ids = array(5,7,40);
        $language_id = $this->data['lang_id'];
        
        if($option_id != 'smart')
        {
            $option_ids = array(intval($option_id));
        }
        
        if($limit == '')
        {
            $limit = 8;
        }
        
        if(empty($q))
        {
            echo json_encode(array());
            exit();
        }

        $results = $this->option_m->get_typeahead($q, $limit, $option_ids, $language_id);
        
        echo json_encode($results);
        //echo '["Electric Light Orchestra", "Elvis Costello", "Eric Clapton"]';
        //exit();
    }
    

    public function ajax ($page_id)
    {
        // Fetch post values
        $address = $this->input->post('address');
        $order = $this->input->post('order');
        $view = $this->input->post('view');
        
        $post_option = array();
        $post_option_sum = ' ';
        foreach($_POST as $key=>$val)
        {
            $tmp_post = $this->input->post($key);
            if(!empty($tmp_post) && strrpos($key, 'tion_') > 0){
                $post_option[substr($key, strrpos($key, 'tion_')+5)] = $tmp_post;
                $post_option_sum.=$tmp_post.' ';
            }
            
            if(is_array($tmp_post))
            {
                $category_num = substr($key, strrpos($key, 'gory_')+5);
                
                foreach($tmp_post as $key=>$val)
                {
                    $post_option['0'.$category_num.'9999'.$key] = $val;
                    $post_option_sum.=$val.' ';
                }
            }
            
        }
        // End fetch post values       
        
        $lang_id = $this->data['lang_id'];
        
        /* Define order */
        if(empty($order))$order='id DESC';
        
        $this->data['order_dateASC_selected'] = '';
        if($order=='id ASC')
            $this->data['order_dateASC_selected'] = 'selected';
            
        $this->data['order_dateDESC_selected'] = '';
        if($order=='id DESC')
            $this->data['order_dateDESC_selected'] = 'selected';

        $this->data['order_livingarea_selected'] = '';
        if($order=='livingArea')
            $this->data['order_livingarea_selected'] = 'selected';
        /* End define order */
        
        /* Define view */
        if(empty($view))$view='grid';
        
        $this->data['view_grid_selected'] = '';
        $this->data['has_view_grid'] = array();
        if($view=='grid')
        {
            $this->data['view_grid_selected'] = 'active';
            $this->data['has_view_grid'][] = array('view' => 'grid');
        }
        
        $this->data['view_list_selected'] = '';
        $this->data['has_view_list'] = array();
        if($view=='list')
        {
            $this->data['view_list_selected'] = 'active';
            $this->data['has_view_list'][] = array('view' => 'list');
        }
        /* End define view */  
        
        /* Define purpose */
        $this->data['is_purpose_rent'] = array();
        $this->data['is_purpose_sale'][] = array('count'=>'1');
        
        if(strpos($post_option_sum, lang_check('Rent')) !== FALSE)
        {
            $this->data['is_purpose_sale'] = array();
            $this->data['is_purpose_rent'][] = array('count'=>'1');
        }
        /* End define purpose */
        
        /* Pagination configuration */ 
        $config['base_url'] = $this->data['ajax_load_url'];
        $config['total_rows'] = 200;
        $config['per_page'] = config_item('per_page');
        $config['uri_segment'] = 5;
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        /* End Pagination */
        
        $options = $this->option_m->get_options($lang_id);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);

        /* Search */
        $this->db->distinct();
        $this->db->select('property.id as id, property.gps, property.is_featured, property.address, property.date, property.repository_id');
        $this->db->from('property');
        $this->db->where('property.is_activated', 1);
        if(!empty($address)){
            $this->db->like('property.address', $address);
        }
        foreach($post_option as $key=>$val)
        {
            if(is_numeric($key) || $key == 'smart')
                $this->db->like('property.search_values', $val);
        }
        
        //$this->db->order_by('property.'.$order);
        $this->db->order_by('property.is_featured DESC, property.'.$order);

        //$this->db->limit($config['per_page'], $this->data['pagination_offset']);
        $query = $this->db->get();
                
        $res_array = array();
        if ($query->num_rows() > 0)
        {
            $res_array = $query->result_array();
            
            $this->_custom_search_filtering($res_array, $options, $post_option);
        }
        
        /* Pagination in query */
        $config['total_rows'] = count($res_array);
        $res_array_all = $res_array;
        
        // Pagination filtering
        $i=0;
        foreach($res_array as $key=>$row)
        {
            if($this->data['pagination_offset'] > $i)
                unset($res_array[$key]);
            
            if($this->data['pagination_offset']+$config['per_page'] <= $i)
                unset($res_array[$key]);
            
            $i++;
        }
        
        /* End Pagination */
        
        $data = array();
        
        $this->data['options_name'] = array();
        $this->data['options_suffix'] = array();
        foreach($options_name as $key=>$row)
        {
            $this->data['options_name_'.$row->option_id] = $row->option;
            $this->data['options_suffix_'.$row->option_id] = $row->suffix;
        }
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);
            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
            }
        }
        
        /* End fetch files */
        
        $this->data['has_no_results'] = array();
        if(count($res_array) == 0)
            $this->data['has_no_results'][] = array('count'=>count($res_array));
        
        /* Get all estates data for json */
        $this->data['results_json'] = array();
        foreach($res_array_all as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr['id'];
            $estate['gps'] = $estate_arr['gps'];
            $estate['address'] = $estate_arr['address'];
            $estate['date'] = $estate_arr['date'];
            $estate['repository_id'] = $estate_arr['repository_id'];
            $estate['is_featured'] = $estate_arr['is_featured'];
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_arr['id']][$row2->option_id]))
                {
                    $row1 = $options[$estate_arr['id']][$row2->option_id];
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
            
            // Url to preview
            if(isset($options[$estate_arr['id']][10]))
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_arr['id']][10]));
            }
            else
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_arr['repository_id']]))
            {
                $estate['thumbnail_url_json'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url_json'] = base_url('templates/'.$this->data['settings_template']).'/assets/img/no_image.jpg';
            }
            

            $estate_obj = new StdClass;
            $estate_obj_options = new StdClass;
            $estate_obj_options->icon = base_url('templates/'.$this->data['settings_template']).'/'.$estate['icon'];
            $gps_coo = explode(', ', $estate_arr['gps']);
            if(count($gps_coo) == 2)$estate_obj->latLng = array(floatval($gps_coo[0]), floatval($gps_coo[1]));
            $estate_obj->options = $estate_obj_options;
            if(!isset($estate['option_2']))$estate['option_2'] = '{option_2}';
            if(!isset($estate['option_4']))$estate['option_4'] = '{option_4}';
            $estate_obj->data = "<img style=\"width: 150px; height: 100px;\" src=\"".$estate['thumbnail_url_json']."\" /><br />".
                                $estate_arr['address']."<br />".$estate['option_2']."<br /><span class=\"label label-info\">&nbsp;&nbsp;".$estate['option_4']."&nbsp;&nbsp;</span>".
                                "<br /><a href=\"".$estate['url']."\">".lang('Details')."</a>";

            $this->data['results_json'][] = $estate_obj;
        }
        
        $results_center = calculateCenterArray($res_array_all);

        /* Get all estates data */
        $this->data['results'] = array();
        foreach($res_array as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr['id'];
            $estate['gps'] = $estate_arr['gps'];
            $estate['address'] = $estate_arr['address'];
            $estate['date'] = $estate_arr['date'];
            $estate['repository_id'] = $estate_arr['repository_id'];
            $estate['is_featured'] = $estate_arr['is_featured'];
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_arr['id']][$row2->option_id]))
                {
                    $row1 = $options[$estate_arr['id']][$row2->option_id];
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    
                    if(!empty($row1))
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                }
            }
            
            // Url to preview
            if(isset($options[$estate_arr['id']][10]))
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_arr['id']][10]));
            }
            else
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_arr['repository_id']]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
                $estate['thumbnail_url_json'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
                $estate['thumbnail_url_json'] = base_url('templates/'.$this->data['settings_template']).'/assets/img/no_image.jpg';
            }

            $this->data['results'][] = $estate;
        }
        
        /* Pagination load */ 
        $this->pagination->initialize($config);
        $this->data['pagination_links'] =  $this->pagination->create_links();
        /* End Pagination */
        
        $output = $this->parser->parse($this->data['settings_template'].'/results.php', $this->data, TRUE);
        $output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
        
        echo json_encode(array('results'=>$this->data['results_json'], 'results_center'=>$results_center, 'print' => $output, 'order'=>$order, 'lang_id'=>$lang_id, 'total_rows'=>$config['total_rows']));
        exit();
    }
    
	public function index ()
	{
        $lang_id = $this->data['lang_id'];
        
        // Fetch all files by repository_id
        $files = $this->file_m->get();
        $rep_file_count = array();
        $this->data['page_documents'] = array();
        $this->data['page_images'] = array();
        $this->data['page_files'] = array();
        foreach($files as $key=>$file)
        {
            $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/_blank.png');
            $file->url = base_url('files/'.$file->filename);

            if(file_exists(FCPATH.'files/thumbnail/'.$file->filename))
            {
                $file->thumbnail_url = base_url('files/thumbnail/'.$file->filename);
                $this->data['images_'.$file->repository_id][] = $file;
                
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_images'][] = $file;
                }
            }
            else if(file_exists(FCPATH.'admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png'))
            {
                $file->thumbnail_url = base_url('admin-assets/img/icons/filetype/'.get_file_extension($file->filename).'.png');
                $this->data['documents_'.$file->repository_id][] = $file;
                if($this->temp_data['page']->repository_id == $file->repository_id)
                {
                    $this->data['page_documents'][] = $file;
                }
            }
            
            $this->data['files_'.$file->repository_id][] = $file;

            if($this->temp_data['page']->repository_id == $file->repository_id)
            {
                $this->data['page_files'][] = $file;
            }
        }
        
        // Has attributes
        $this->data['has_page_documents'] = array();
        if(count($this->data['page_documents']))
            $this->data['has_page_documents'][] = array('count'=>count($this->data['page_documents']));
        
        $this->data['has_page_images'] = array();
        if(count($this->data['page_images']))
            $this->data['has_page_images'][] = array('count'=>count($this->data['page_images']));
            
        $this->data['has_page_files'] = array();
        if(count($this->data['page_files']))
            $this->data['has_page_files'][] = array('count'=>count($this->data['page_files']));
        /* End fetch files */
        
        // Get slideshow
        $slideshow = $this->slideshow_m->get_array(NULL, TRUE);
        
        $this->data['slideshow_images'] = array();
        if(isset($this->data['images_'.$slideshow['repository_id']]))
        foreach($this->data['images_'.$slideshow['repository_id']] as $key=>$file)
        {
            $slideshow_image = array();
            $slideshow_image['num'] = $key;
            $slideshow_image['url'] = $file->url;
            $slideshow_image['first_active'] = '';
            if($key==0)$slideshow_image['first_active'] = 'active';
            
            $this->data['slideshow_images'][] = $slideshow_image;
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
        
        /* Define purpose */
        $purpose = '';
        $this->data['purpose_rent_active'] = '';
        $this->data['purpose_sale_active'] = '';
        
        $this->data['is_purpose_rent'] = array();
        $this->data['is_purpose_sale'] = array();
        
        if(strpos($this->temp_data['page']->template, 'rent') !== FALSE)
        {
            $purpose = 'rent';
            $this->data['purpose_rent_active'] = 'active';
            $this->data['is_purpose_rent'][] = array('count'=>'1');
        }
        else if(strpos($this->temp_data['page']->template, 'sale') !== FALSE ||
                strpos($this->temp_data['page']->template, 'home') !== FALSE)
        {
            $purpose = 'sale';
            $this->data['purpose_sale_active'] = 'active';
            $this->data['is_purpose_sale'][] = array('count'=>'1');
        }
        
        /* Get all estates data */
        $estates = $this->estate_m->get_by(array('is_activated' => 1));
        $options = $this->option_m->get_options($this->data['lang_id']);
        
        $this->data['all_estates'] = array();
        $this->data['featured_properties'] = array();
        foreach($estates as $key=>$estate_obj)
        {
            $estate = array();
            $estate['id'] = $estate_obj->id;
            $estate['gps'] = $estate_obj->gps;
            $estate['address'] = $estate_obj->address;
            $estate['date'] = $estate_obj->date;
            $estate['is_featured'] = $estate_obj->is_featured;
            
            // All estate options            
            if(isset($options[$estate_obj->id]))
            foreach($options[$estate_obj->id] as $key1=>$row1)
            {                
                $estate['option_'.$key1] = $row1;
                $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
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
            
            if($estate_obj->is_featured)$this->data['featured_properties'][] = $estate;
            $this->data['all_estates'][] = $estate;
        }
        
        $this->data['all_estates_center'] = calculateCenter($estates);
        
        /* End get all estates data */
        
        $options_name = $this->option_m->get_lang(NULL, FALSE, $this->data['lang_id']);
        
        /* Get last n properties */
        $last_n = 4;
        if(config_item('last_estates_limit'))
            $last_n = config_item('last_estates_limit');
        
        $last_n_estates = $this->estate_m->get_array_by(array('is_activated' => 1), FALSE, $last_n, 'id DESC');
        
        $this->data['last_estates_num'] = $last_n;
        $this->data['last_estates'] = array();
        foreach($last_n_estates as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr['id'];
            $estate['gps'] = $estate_arr['gps'];
            $estate['address'] = $estate_arr['address'];
            $estate['date'] = $estate_arr['date'];
            $estate['repository_id'] = $estate_arr['repository_id'];
            $estate['is_featured'] = $estate_arr['is_featured'];
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_arr['id']][$row2->option_id]))
                {
                    $row1 = $options[$estate_arr['id']][$row2->option_id];
                    $estate['option_'.$key1] = $row1;
                    $estate['option_chlimit_'.$key1] = character_limiter(strip_tags($row1), 80);
                    
                    if(!empty($row1))
                        $estate['has_option_'.$key1][] = array('count'=>count($row1));
                }
            }
            
            // Url to preview
            if(isset($options[$estate_arr['id']][10]))
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_arr['id']][10]));
            }
            else
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_arr['repository_id']]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['last_estates'][] = $estate;
        }
        
        /* Check for tab/purpose select */
        
        foreach($options_name as $key=>$row)
        {
            $this->data['options_val_'.$row->option_id] = $row->values;
        }
        
        $this->select_tab_by_title = '';
        if(isset($this->data['options_val_4']))
        {
            if(!empty($this->data['page_navigation_title']))
            if(strpos(strtolower($this->data['options_val_4']), strtolower($this->data['page_navigation_title'])) !== false)
            {
                $this->select_tab_by_title = strtolower($this->data['page_navigation_title']);
            }
        }
        
        // If no selection, then select first
        if(isset($this->data['options_val_4']))
        //if(strpos(strtolower(' '.$this->data['options_val_4']), strtolower($this->_get_purpose()))  === false)
        if($this->select_tab_by_title == '')
        {
            $vals = explode(',', $this->data['options_val_4']);
            if(count($vals)>0)
            $this->select_tab_by_title = strtolower($vals[0]);
        }

        /* End check for tab/purpose select */

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
                    if(strtolower($this->_get_purpose()) == strtolower($val))$active = 'active';
                    $options_li.= '<li class="'.$active.' cat_'.$key2.'"><a href="#">'.$val.'</a></li>';
                }
                $this->data['options_values_'.$row->option_id] = $options;
                $this->data['options_values_li_'.$row->option_id] = $options_li;
            }
        }

        $this->data['has_no_all_estates'] = array();
        if(count($this->data['all_estates']) == 0)
        {
            $this->data['has_no_all_estates'][] = array('count'=>count($this->data['all_estates']));
        }
        
        $this->data['featured_estates'] = $this->estate_m->get_by(array('is_featured'=>true));
        
        /* End fetch estate */
        
        /* Define order */
        if(empty($order))$order='id DESC';
        
        $this->data['order_dateASC_selected'] = '';
        if($order=='id ASC')
            $this->data['order_dateASC_selected'] = 'selected';
            
        $this->data['order_dateDESC_selected'] = '';
        if($order=='id DESC')
            $this->data['order_dateDESC_selected'] = 'selected';
        
        /* Pagination configuration */ 
        $config['base_url'] = $this->data['ajax_load_url'];
        $config['total_rows'] = 200;
        $config['per_page'] = config_item('per_page');
        $config['uri_segment'] = 5;
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        /* End Pagination */
        
        $options = $this->option_m->get_options($lang_id);
        $options_name = $this->option_m->get_lang(NULL, FALSE, $lang_id);
        
        /* Search */
        $this->db->distinct();
        $this->db->select('property.id, property.gps, property.is_featured, property.address, property.date, property.repository_id');
        $this->db->from('property');
        $this->db->where('property.is_activated', 1);
        /* ORDER_BY_PRICE */
        //$this->db->join('property_value', 'property.id = property_value.property_id', 'inner');
        //$this->db->where('property_value.option_id', 36);
        /* /ORDER_BY_PRICE */
        $lang_purpose = lang_check(ucfirst($purpose));
        if($this->select_tab_by_title != '')
            $lang_purpose = $this->select_tab_by_title;
        $this->db->like('property.search_values', $lang_purpose);
        if(!empty($this->data['search_query']))
        {
            $this->db->like('property.search_values', $this->data['search_query']);
        }

        /* Pagination in query */
        $config['total_rows'] = $this->db->count_all_results();
        /* ORDER_BY_PRICE */
        //$query = $this->db->get();
        //$config['total_rows'] = $query->num_rows();
        /* /ORDER_BY_PRICE */
        $this->db->limit($config['per_page'], $this->data['pagination_offset']);
        /* End Pagination */
        
        $this->db->distinct();
        $this->db->select('property.id, property.gps, property.is_featured, property.address, property.date, property.repository_id');
        $this->db->from('property');
        $this->db->where('property.is_activated', 1);
        /* ORDER_BY_PRICE */
        //$this->db->join('property_value', 'property.id = property_value.property_id', 'inner');
        //$this->db->where('property_value.option_id', 36);
        /* /ORDER_BY_PRICE */
        
        $lang_purpose = lang_check(ucfirst($purpose));
        if($this->select_tab_by_title != '')
            $lang_purpose = $this->select_tab_by_title;
        $this->db->like('property.search_values', $lang_purpose);
        if(!empty($this->data['search_query']))
        {
            $this->db->like('property.search_values', $this->data['search_query']);
        }
        //$this->db->order_by('property.'.$order);
        $this->db->order_by('property.is_featured DESC, property.'.$order);
        /* ORDER_BY_PRICE */
        //$this->db->order_by('value DESC');
        /* /ORDER_BY_PRICE */
        $query = $this->db->get();
        
        $res_array = array();
        if ($query->num_rows() > 0)
        {
            $res_array = $query->result_array();
        }
        
        $this->data['has_no_results'] = array();
        if(count($res_array) == 0)
            $this->data['has_no_results'][] = array('count'=>count($res_array));
        
        /* Get all estates data */
        $this->data['results'] = array();
        foreach($res_array as $key=>$estate_arr)
        {
            $estate = array();
            $estate['id'] = $estate_arr['id'];
            $estate['gps'] = $estate_arr['gps'];
            $estate['address'] = $estate_arr['address'];
            $estate['date'] = $estate_arr['date'];
            $estate['repository_id'] = $estate_arr['repository_id'];
            $estate['is_featured'] = $estate_arr['is_featured'];
            
            foreach($options_name as $key2=>$row2)
            {
                $key1 = $row2->option_id;
                $estate['has_option_'.$key1] = array();
                
                if(isset($options[$estate_arr['id']][$row2->option_id]))
                {
                    $row1 = $options[$estate_arr['id']][$row2->option_id];
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
            
            // Url to preview
            if(isset($options[$estate_arr['id']][10]))
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code'].'/'.url_title_cro($options[$estate_arr['id']][10]));
            }
            else
            {
                $estate['url'] = site_url($this->data['listing_uri'].'/'.$estate_arr['id'].'/'.$this->data['lang_code']);
            }
            
            // Thumbnail
            if(isset($this->data['images_'.$estate_arr['repository_id']]))
            {
                $estate['thumbnail_url'] = $this->data['images_'.$estate_arr['repository_id']][0]->thumbnail_url;
            }
            else
            {
                $estate['thumbnail_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['results'][] = $estate;
        }
        
        
        
        /* Pagination load */ 
        $this->pagination->initialize($config);
        $this->data['pagination_links'] =  $this->pagination->create_links();
        /* End Pagination */
        
        /* Fetch all agents */
        
        $all_agents = $this->user_m->get_by(array('type'=>'AGENT'));
        
        $this->data['all_agents'] = array();
        foreach($all_agents as $key=>$agent_obj)
        {
            $agent = array();
            $agent['name_surname'] = $agent_obj->name_surname;
            $agent['phone'] = $agent_obj->phone;
            $agent['mail'] = $agent_obj->mail;
            $agent['address'] = $agent_obj->address;
            
            if(isset($this->data['images_'.$agent_obj->repository_id]))
            {
                $agent['image_url'] = $this->data['images_'.$agent_obj->repository_id][0]->thumbnail_url;
            }
            else
            {
                $agent['image_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['all_agents'][] = $agent;
        }
        
        $this->data['has_agents'] = array();
        if(count($all_agents))
            $this->data['has_agents'][] = array('count'=>count($all_agents));
            
        /* End fetch all agents */
        
        /* Validation for contact */
        $rules = array(
            'firstname' => array('field'=>'firstname', 'label'=>'lang:FirstLast', 'rules'=>'trim|required|xss_clean'),
            'email' => array('field'=>'email', 'label'=>'lang:Email', 'rules'=>'trim|required|xss_clean'),
            'phone' => array('field'=>'phone', 'label'=>'lang:Phone', 'rules'=>'trim|required|xss_clean'),
            'message' => array('field'=>'message', 'label'=>'lang:Message', 'rules'=>'trim|required|xss_clean')
       );
       
       if(isset($_POST['question']))
       {
            unset($rules['message']);
            $rules['question'] = array('field'=>'question', 'label'=>'lang:Question', 'rules'=>'trim|required|xss_clean');
       }
       
       $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            $data = $this->page_m->array_from_post(array('firstname', 'email', 'phone', 'message'));

            // Send email
            $this->load->library('email');
            
            $this->email->from($this->data['settings_noreply'], 'Web page');
            $this->email->to($this->data['settings_email']);
            
            $this->email->subject(lang_check('Message from real-estate web'));
            
            if(isset($_POST['question']))
            {
                $this->load->model('qa_m');
                
                $data_t = $this->page_m->array_from_post(array('firstname', 'email', 'phone', 'question'));
                
                $data = array();
                $data['is_readed'] = 0;
                $data['date'] = date('Y-m-d H:i:s');
                $data['type'] = 'QUESTION';
                $data['answer_user_id'] = 0;
                $data['parent_id'] = 0;
                
                $data_lang = array();
                $data_lang['question_'.$lang_id] = $data_t['question'];
                
                $id = $this->qa_m->save_with_lang($data, $data_lang, NULL);
                $this->email->subject(lang_check('Expert question from real-estate web'));
    
                $data['name_surname'] = $data_t['firstname'];
                $data['phone'] = $data_t['phone'];
                $data['mail'] = $data_t['email'];
            }

            $message='';
            foreach($_POST as $key=>$value){
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
        $this->data['form_error_question'] = form_error('question')==''?'':'error';
        
        // Form values
        $this->data['form_value_firstname'] = set_value('firstname', '');
        $this->data['form_value_email'] = set_value('email', '');
        $this->data['form_value_phone'] = set_value('phone', '');
        $this->data['form_value_message'] = set_value('message', '');
        $this->data['form_value_question'] = set_value('question', '');
        
        /* End validation for contact */
        
        $page_id = $this->data['page_id'];
        
        /* {ARTICLES} */
        // Fetch all pages
        $this->data['news_articles'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, array('parent_id' => $page_id, 'type'=>'ARTICLE'), null, '', 'order');

        /* {/ARTICLES} */
        
        /* {MODULE_NEWS} */
        
        $category_id = 0;
        
        // Check for contained category/parent_id
        $news_category = $this->page_m->get_contained_news_category($page_id);
        $cat_merge = array();
        if(count($news_category)>0)
        {
            $cat_merge = array('parent_id' => $news_category->id);
            $category_id = $news_category->id;
        }
        
        $category_id_get = $this->input->get('cat', TRUE);
        if(!empty($category_id_get))
        {
            $cat_merge = array('parent_id' => $category_id_get);
            $category_id = $category_id_get;
        }
        
        $pagination_offset=0;
        
        // Fetch all pages
        $this->data['page_languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['categories'] = $this->page_m->get_no_parents_news_category($lang_id);
        $this->data['news_module_all'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, array_merge($cat_merge, array('type'=>'MODULE_NEWS_POST')), null, '', 'date_publish DESC');
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url('news/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$category_id.'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['news_module_all']);
        $config_2['per_page'] = config_item('per_page');
        $config_2['uri_segment'] = 5;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul>';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_2 = new CI_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['news_pagination'] = $pagination_2->create_links();
        
        $this->data['news_module_all'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'MODULE_NEWS_POST')), 
                                                          $config_2['per_page'], $pagination_offset, 'date_publish DESC');
        
        $this->data['news_module_latest_5'] = $this->page_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'MODULE_NEWS_POST')), 
                                                          5, 0, 'date_publish DESC');
        
        /* {/MODULE_NEWS} */
        
        /* {MODULE_ADS} */
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
        /* {/MODULE_ADS} */
        
        /* {MODULE_SHOWROOM} */
        
        $this->load->model('showroom_m');
        
        $category_id = 0;
        
        // Check for contained category/parent_id
        $showroom_category = $this->showroom_m->get_contained_showroom_category($page_id);
        $cat_merge = array();
        if(count($showroom_category)>0)
        {
            $cat_merge = array('parent_id' => $showroom_category->id);
            $category_id = $showroom_category->id;
        }
        
        $category_id_get = $this->input->get('cat', TRUE);
        if(!empty($category_id_get))
        {
            $cat_merge = array('parent_id' => $category_id_get);
            $category_id = $category_id_get;
        }
        
        $pagination_offset=0;
        
        // Fetch all pages
        $this->data['categories_showroom'] = $this->showroom_m->get_no_parents_showrooms_category($lang_id);
        $this->data['showroom_module_all'] = $this->showroom_m->get_lang(NULL, FALSE, $lang_id, array_merge($cat_merge, array('type'=>'COMPANY')), null, '', 'date_publish DESC');
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url('showroom/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$category_id.'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['showroom_module_all']);
        $config_2['per_page'] = config_item('per_page');
        $config_2['uri_segment'] = 5;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul>';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_2 = new CI_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['showroom_pagination'] = $pagination_2->create_links();
        
        $this->data['showroom_module_all'] = $this->showroom_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'COMPANY')), 
                                                          $config_2['per_page'], $pagination_offset, 'date_publish DESC');
        
        $this->data['showroom_module_latest_5'] = $this->showroom_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'COMPANY')), 
                                                          5, 0, 'date_publish DESC');
        
        /* {/MODULE_SHOWROOM} */
        
        /* {MODULE_Q&A} */
        
        $this->load->model('qa_m');
        
        $category_id = 0;
        
        // Check for contained category/parent_id
        $expert_category = $this->qa_m->get_contained_expert_category($page_id);
        $cat_merge = array();
        if(count($expert_category)>0)
        {
            $cat_merge = array('parent_id' => $expert_category->id);
            $category_id = $expert_category->id;
        }
        
        $category_id_get = $this->input->get('cat', TRUE);
        if(!empty($category_id_get))
        {
            $cat_merge = array('parent_id' => $category_id_get);
            $category_id = $category_id_get;
        }
        
        $pagination_offset=0;
        
        // Fetch all pages
        $this->data['categories_expert'] = $this->qa_m->get_no_parents_expert_category($lang_id);
        $this->data['expert_module_all'] = $this->qa_m->get_lang(NULL, FALSE, $lang_id, array_merge($cat_merge, array('type'=>'QUESTION', 'is_readed'=>1)), null, '', 'date_publish DESC');
        
        /* Pagination configuration */ 
        $config_2['base_url'] = site_url('expert/ajax/'.$this->data['lang_code'].'/'.$this->data['page_id'].'/'.$category_id.'/');
        //$config_2['first_url'] = site_url($this->uri->uri_string());
        $config_2['total_rows'] = count($this->data['expert_module_all']);
        $config_2['per_page'] = config_item('per_page');
        $config_2['uri_segment'] = 5;
    	$config_2['num_tag_open'] = '<li>';
    	$config_2['num_tag_close'] = '</li>';
        $config_2['full_tag_open'] = '<ul>';
        $config_2['full_tag_close'] = '</ul>';
        $config_2['cur_tag_open'] = '<li class="active"><span>';
        $config_2['cur_tag_close'] = '</span></li>';
    	$config_2['next_tag_open'] = '<li>';
    	$config_2['next_tag_close'] = '</li>';
    	$config_2['prev_tag_open'] = '<li>';
    	$config_2['prev_tag_close'] = '</li>';
        /* End Pagination */

        //$this->pagination->initialize($config_2);
        $pagination_2 = new CI_Pagination($config_2);
        //$pagination_2->initialize($config_2);
        $this->data['expert_pagination'] = $pagination_2->create_links();
        
        $this->data['expert_module_all'] = $this->qa_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'QUESTION', 'is_readed'=>1)), 
                                                          $config_2['per_page'], $pagination_offset, 'date_publish DESC');
        
        $this->data['expert_module_latest_5'] = $this->qa_m->get_lang(NULL, FALSE, $lang_id, 
                                                          array_merge($cat_merge, array('type'=>'QUESTION', 'is_readed'=>1)), 
                                                          5, 0, 'date_publish DESC');
        
        // Fetch all experts
        $all_experts = $this->user_m->get_by(array('qa_id !='=>0, 'type !=' => 'USER'));
        
        $this->data['all_experts'] = array();
        foreach($all_experts as $key=>$expert_obj)
        {
            $agent = array();
            $agent['name_surname'] = $expert_obj->name_surname;
            $agent['phone'] = $expert_obj->phone;
            $agent['mail'] = $expert_obj->mail;
            $agent['address'] = $expert_obj->address;
            
            if(isset($this->data['images_'.$expert_obj->repository_id]))
            {
                $agent['image_url'] = $this->data['images_'.$expert_obj->repository_id][0]->thumbnail_url;
            }
            else
            {
                $agent['image_url'] = 'assets/img/no_image.jpg';
            }

            $this->data['all_experts'][$expert_obj->id] = $agent;
        }
        
        /* {/MODULE_Q&A} */
        
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
        

        $output = $this->parser->parse($this->data['settings_template'].'/'.$this->temp_data['page']->template.'.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
	}
    
	public function gps_check($str)
	{
        $gps_coor = explode(', ', $str);
        
        if(count($gps_coor) != 2)
        {
        	$this->form_validation->set_message('gps_check', lang_check('Please check GPS coordinates'));
        	return FALSE;
        }
        
        if(!is_numeric($gps_coor[0]) || !is_numeric($gps_coor[1]))
        {
        	$this->form_validation->set_message('gps_check', lang_check('Please check GPS coordinates'));
        	return FALSE;
        }
        
        return TRUE;
	}

}