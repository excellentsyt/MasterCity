<?php

class Settings extends Admin_Controller {
	
    public function __construct(){
		parent::__construct();
        $this->load->model('settings_m');
        $this->data['settings'] = $this->settings_m->get_fields();
	}
    
    public function contact()
    {
        $this->index();
    }
    
    public function index() 
    {
        // Set up the form
        $rules = $this->settings_m->rules_contact;
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings');
                exit();
            }
            
            $data = $this->settings_m->array_from_post($this->settings_m->get_post_from_rules($rules));
            $this->settings_m->save_settings($data);
            
            redirect('admin/settings');
        }
        
    	$this->data['subview'] = 'admin/settings/contact';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function system() 
    {
        // Set up the form
        $rules = $this->settings_m->rules_system;
        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/system');
                exit();
            }
            
            $data = $this->settings_m->array_from_post($this->settings_m->get_post_from_rules($rules));
            $this->settings_m->save_settings($data);
            
            redirect('admin/settings/system');
        }
        
    	$this->data['subview'] = 'admin/settings/system';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function template() 
    {
        // Set up the form
        $rules = $this->settings_m->rules_template;
        $this->form_validation->set_rules($rules);
        
        $this->data['templates_available'] = array();
        $langDirectory = opendir(FCPATH.'templates/');
        // get each template
        while($templateName = readdir($langDirectory)) {
            if ($templateName != "." && $templateName != "..") {
                $this->data['templates_available'][$templateName] = $templateName;
            }
        }
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/template');
                exit();
            }
            
            $data = $this->settings_m->array_from_post($this->settings_m->get_post_from_rules($rules));

            $this->settings_m->save_settings($data);
            
            redirect('admin/settings/template');
        }
        
    	$this->data['subview'] = 'admin/settings/template';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language() 
    {
        $this->data['languages'] = $this->language_m->get();
        
    	$this->data['subview'] = 'admin/settings/language';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language_files($id) 
    {
        $this->data['language_files'] = array();
        $this->data['language_id'] = $id;
        
        // template files
        $directory = opendir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && strpos($templateName, '.php')>0) {
                $file = array('path'=> FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/'.$templateName, 
                              'filename'=>$templateName, 'important_for'=>'Frontend', 'folder'=>'template');

                $this->data['language_files'][] = $file;
            }
        }
        
        // application files
        $directory = opendir(APPPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && strpos($templateName, '.php')>0) {
                $file = array('path'=> APPPATH.'language/english/'.$templateName, 
                              'filename'=>$templateName, 'important_for'=>'Backend', 'folder'=>'application');
                
                if(strpos($templateName, 'vali')>0)
                {
                    $file = array('path'=> APPPATH.'language/english/'.$templateName, 
                                  'filename'=>$templateName, 'important_for'=>'Frontend & Backend', 'folder'=>'application');
                }


                $this->data['language_files'][] = $file;
            }
        }
        
        // system files
        $directory = opendir(BASEPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && strpos($templateName, '.php')>0) {
                $file = array('path'=> BASEPATH.'language/english/'.$templateName, 
                              'filename'=>$templateName, 'important_for'=>'System', 'folder'=>'system');

                $this->data['language_files'][] = $file;
            }
        }
        
    	$this->data['subview'] = 'admin/settings/language_files';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language_edit_file($lang_id, $file_name)
    {
        $this->load->helper('security');
        
        $language_current = $this->language_m->get_name($lang_id);
        $this->data['message'] = '';
        
        $folder = substr($file_name, 0, stripos($file_name, '-'));
        $file   = substr($file_name, stripos($file_name, '-')+1);
        $path_english   = '';
        $path_current   = '';
        
        if($folder == 'system')
        {
            $path_english = BASEPATH.'language/english/'.$file;
            $path_current = BASEPATH.'language/'.$language_current.'/'.$file;
        }
        else if($folder == 'application')
        {
            $path_english = APPPATH.'language/english/'.$file;
            $path_current = APPPATH.'language/'.$language_current.'/'.$file;
        }
        else if($folder == 'template')
        {
            $path_english = FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/'.$file;
            $path_current = FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_current.'/'.$file;
        }
        
        $this->data['file'] = $file;
        $this->data['lang_id'] = $lang_id;
        $this->data['lang_name'] = $language_current;
        
        $lang=array();
        include $path_english;
        $this->data['language_translations_english'] = $lang;
        
        $lang=array();
        if(file_exists($path_current)){
            include $path_current;
            $this->data['language_translations_current'] = $lang;
        }
        
        if(count($_POST) > 0)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/language_edit_file/'.$lang_id.'/'.$file_name);
                exit();
            }
            
            if(!is_writable($path_current))
            {
                $this->session->set_flashdata('error', 
                        'File '.$path_current.' is not writable<br />');
                redirect('admin/settings/language_edit_file/'.$lang_id.'/'.$file_name);
                exit();
            }
            
            // Save file
            $file_content = '<?php '."\n\n";
            
            $previous = 't';
            foreach($this->data['language_translations_english'] as $key=>$val)
            {
                $lang_val = $val;
                if(isset($_POST[md5($key)]))
                    $lang_val = $_POST[md5($key)];
                
                $lang_val = xss_clean($lang_val);
                $lang_val = str_replace('"', '\"', $lang_val);
                $lang_val = str_replace('$', '\\$', $lang_val);
                
                $key = str_replace('\'', '\\\'', $key);
                $key = str_replace('$', '\\$', $key);
                
                if(empty($previous) && !empty($lang_val))
                    $file_content.= "\n";
                
                $file_content.= '$lang[\''.$key.'\'] = "'.$lang_val.'";'."\n";
                $previous = $lang_val;
            }
            $file_content.= "\n".'?>';
            
            $message = '';
            if (!$handle = fopen($path_current, 'w')) {
                 $message = lang('cannot_open_file')." ($path_current)";
                 exit;
            }
        
            // Write $somecontent to our opened file.
            if (fwrite($handle, $file_content) === FALSE) {
                $message = lang('cannot_write_file')." ($path_current)";
                exit;
            }
    
            fclose($handle);
            
            if($message == '')
            {
                $this->session->set_flashdata('message', 
                        '<p class="label label-success validation">Changes saved</p>');
                redirect('admin/settings/language_edit_file/'.$lang_id.'/'.$file_name);
            }
            else
            {
                $this->data['message'] = '<p class="label label-important validation">'.$message.'</p>';
            }
            
        }
        
    	$this->data['subview'] = 'admin/settings/language_edit_file';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    public function language_edit($id = NULL) 
    {
	    // Fetch a record or set a new one
	    if($id)
        {
            $this->data['language'] = $this->language_m->get($id);
            count($this->data['language']) || $this->data['errors'][] = 'Could not be found';
        }
        else
        {
            $this->data['language'] = $this->language_m->get_new();
        }
        
        $this->data['available_langs'] = lang_check('Alredy translated').':&nbsp;&nbsp;';
        // language directory files
        $directory = opendir(FCPATH.'templates/'.$this->data['settings']['template'].'/language');
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != "..") {
                // Check if is dir
                if(is_dir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$templateName))
                {
                    $this->data['available_langs'].='<span class="available-langs-sel badge badge-info">'.$templateName.'</span>&nbsp;&nbsp;';
                }
            }
        }

        // Set up the form
        $rules = $this->language_m->rules_admin;
        if(!$id || (isset($_POST['language']) && $this->data['language']->language != $_POST['language'])){
            $rules['language']['rules'].='|is_unique[language.language]';
        }

        $this->form_validation->set_rules($rules);
        
        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/settings/language');
                exit();
            }

            $data = $this->settings_m->array_from_post($this->language_m->get_post_from_rules($rules));
            
            if($data['is_frontend'] == '0')
            {
                //Check if there is one more visible language
                if($this->language_m->count_visible($id) == 0)
                {
                    $data['is_frontend'] = '1';
                }
            }
            
            $message = $this->_check_language_files($data['language']);
            
            if(!empty($message))
            {
                $this->session->set_flashdata('error', $message);
            }
            
            $this->language_m->save($data, $id);
            
            redirect('admin/settings/language');
        }
        
    	$this->data['subview'] = 'admin/settings/language_edit';
    	$this->load->view('admin/_layout_main', $this->data);
    }
    
    private function _check_language_files($language_name)
    {
        $res = true;
        $message = '';
        
        if(!file_exists(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'))
            $res = mkdir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/');
        
        if(!$res)
        {
            $message = 'Failed to make dir: '.FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/';
            return $message;
        }
        
        // template files
        $directory = opendir(FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && strpos($templateName, '.php')>0) {
                // Check if file not exists, copy it from english
                if(!file_exists(FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'.$templateName))
                    $res = copy(FCPATH.'templates/'.$this->data['settings']['template'].'/language/english/'.$templateName,
                                FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'.$templateName);
                
                if(!$res)
                {
                    $message = 'Failed to create file: '.FCPATH.'templates/'.$this->data['settings']['template'].'/language/'.$language_name.'/'.$templateName;
                    return $message;
                }
            }
        }
        
        if(!file_exists(APPPATH.'language/'.$language_name.'/'))
            $res = mkdir(APPPATH.'language/'.$language_name.'/');
            
        if(!$res)
        {
            $message = 'Failed to make dir: '.APPPATH.'language/'.$language_name.'/';
            return $message;
        }
        
        // application files
        $directory = opendir(APPPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && strpos($templateName, '.php')>0) {
                // Check if file not exists, copy it from english
                if(!file_exists(APPPATH.'language/'.$language_name.'/'.$templateName))
                    $res = copy(APPPATH.'language/english/'.$templateName,
                                APPPATH.'language/'.$language_name.'/'.$templateName);
                
                if(!$res)
                {
                    $message = 'Failed to create file: '.APPPATH.'language/'.$language_name.'/'.$templateName;
                    return $message;
                }
            }
        }
        
        if(!file_exists(BASEPATH.'language/'.$language_name.'/'))
            $res = mkdir(BASEPATH.'language/'.$language_name.'/');
        
        if(!$res)
        {
            $message = 'Failed to make dir: '.BASEPATH.'language/'.$language_name.'/';
            return $message;
        }
        
        // system files
        $directory = opendir(BASEPATH.'language/english/');
        // get each template
        while($templateName = readdir($directory)) {
            if ($templateName != "." && $templateName != ".." && strpos($templateName, '.php')>0) {
                // Check if file not exists, copy it from english
                if(!file_exists(BASEPATH.'language/'.$language_name.'/'.$templateName))
                    $res = copy(BASEPATH.'language/english/'.$templateName,
                                BASEPATH.'language/'.$language_name.'/'.$templateName);
                
                if(!$res)
                {
                    $message = 'Failed to create file: '.BASEPATH.'language/'.$language_name.'/'.$templateName;
                    return $message;
                }
            }
        }
        
        return $message;
    }
    
    public function language_delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/settings/language');
            exit();
        }
        
        $language = $this->language_m->get($id);
        if( $language->is_locked )
        {
            $this->session->set_flashdata('error', 
                    lang_check('Language locked, can\'t be deleted but you can change it!'));
            redirect('admin/settings/language');
            exit();
        }
       
		$this->language_m->delete($id);
        redirect('admin/settings/language');
	}
    
}