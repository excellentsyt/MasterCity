<?php

class Estate extends Admin_Controller
{
    
	public function __construct()
    {
		parent::__construct();
        $this->load->model('estate_m');
        $this->load->model('option_m');
        $this->load->model('file_m');
        
        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
	}
    
    public function index($pagination_offset=0)
	{
	    $this->load->library('pagination');
        
        // Fetch all estates
        $this->data['estates'] = $this->estate_m->get_join();
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options'] = $this->option_m->get_options($this->data['content_language_id']);
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname'/*, array('type'=>'AGENT')*/);

        $config['base_url'] = site_url('admin/estate/index');
        $config['uri_segment'] = 4;
        $config['total_rows'] = count($this->data['estates']);
        $config['per_page'] = 20;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        
        $this->data['estates'] = $this->estate_m->get_join($config['per_page'], $pagination_offset);
        
        // Load view
		$this->data['subview'] = 'admin/estate/index';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function edit($id = NULL)
	{
	    // Fetch a page or set a new one
	    if($id)
        {
            $this->data['estate'] = $this->estate_m->get_dynamic($id);
            
            if(count($this->data['estate']) == 0)
            {
                $this->data['errors'][] = 'Estate could not be found';
                redirect('admin/estate');
            }
            
            //Check if user have permissions
            if($this->session->userdata('type') != 'ADMIN')
            {
                if($this->data['estate']->agent == $this->session->userdata('id'))
                {
                    
                }
                else
                {
                    redirect('admin/estate');
                }
            }
            
            // Fetch file repository
            $repository_id = $this->data['estate']->repository_id;
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>'estate_m'));
                
                // Update page with new repository_id
                $this->estate_m->save(array('repository_id'=>$repository_id), $this->data['estate']->id);
            }
        }
        else
        {
            $this->data['estate'] = $this->estate_m->get_new();
        }
        
		// Pages for dropdown
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        
        // Get available agents
        $this->data['available_agent'] = $this->user_m->get_form_dropdown('name_surname', array('type'=>'AGENT'));
        
        $this->data['available_agent'][''] = lang_check('Current user');
        
        // Get all options
        foreach($this->option_m->languages as $key=>$val){
            $this->data['options_lang'][$key] = $this->option_m->get_lang(NULL, FALSE, $key);
        }
        $this->data['options'] = $this->option_m->get_lang(NULL, FALSE, $this->data['content_language_id']);
        
        // Id's for key adjustments 
        // TODO: better solution needed, this is just hotfix
        $options = $this->data['options'];
        $this->data['options'] = array();
        foreach($options as $option_key=>$option_row)
        {
            $this->data['options'][$option_row->option_id] = $option_row;
        }
        
        // For other langs
        foreach($this->option_m->languages as $key=>$val){
            $options_key = $this->data['options_lang'][$key];
            $this->data['options_lang'][$key] = array();
            foreach($options_key as $option_key=>$option_row)
            {
                $this->data['options_lang'][$key][$option_row->option_id] = $option_row;
            }
        }
        // End id's for key adjustments
        
        
        $options_data = array();
        foreach($this->option_m->get() as $key=>$val)
        {
            $options_data[$val->id][$val->type] = 'true';
        }
        
        // Add rules for dynamic options
        $rules_dynamic = array();
        foreach($this->option_m->languages as $key_lang=>$val_lang){
            foreach($this->data['options'] as $key_option=>$val_option){
                $rules_dynamic['option'.$val_option->id.'_'.$key_lang] = 
                    array('field'=>'option'.$val_option->id.'_'.$key_lang, 'label'=>$val_option->option, 'rules'=>'trim');
                //if($id == NULL)$this->data['estate']->{'option'.$val_option->id.'_'.$key_lang} = '';
                if(!isset($this->data['estate']))$this->data['estate']->{'option'.$val_option->id.'_'.$key_lang} = '';
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

            if(file_exists(FCPATH.'/files/thumbnail/'.$file->filename))
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
        $this->form_validation->set_rules(array_merge($rules, $rules_dynamic));

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/estate/edit/'.$id);
                exit();
            }
            
            $data = $this->estate_m->array_from_post(array('gps', 'date', 'address', 'is_featured', 'is_activated'));
            $dynamic_data = $this->estate_m->array_from_post(array_keys($rules_dynamic));
            
            $data['search_values'] = $data['address'];
            foreach($dynamic_data as $key=>$val)
            {
                $pos = strpos($key, '_');
                $option_id = substr($key, 6, $pos-6);
                $language_id = substr($key, $pos+1);
                
                if(!isset($options_data[$option_id]['TEXTAREA']) && !isset($options_data[$option_id]['CHECKBOX'])){
                    $data['search_values'].=' '.$val;
                }
                
                // TODO: test check, values for each language for selected checkbox
                if(isset($options_data[$option_id]['CHECKBOX'])){
                    if($val == 'true')
                    {
                        foreach($this->option_m->languages as $key_lang=>$val_lang){
                            foreach($this->data['options_lang'][$key_lang] as $key_option=>$val_option){
                                if($val_option->id == $option_id && $language_id == $key_lang)
                                {
                                    $data['search_values'].=' true'.$val_option->option;
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
            
            $this->generate_sitemap();
            
            $this->session->set_flashdata('message', 
                    '<p class="label label-success validation">'.lang_check('Changes saved').'</p>');
            
            redirect('admin/estate/edit/'.$insert_id);
        }
        
        // Load the view
		$this->data['subview'] = 'admin/estate/edit';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function values_correction(&$str)
    {
        $str = str_replace(', ', ',', $str);
        
        return TRUE;
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
    
    public function delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/estate');
            exit();
        }
       
		$this->estate_m->delete($id);
        redirect('admin/estate');
	}
    
    public function options()
	{
        // Fetch all estates
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');
        $this->data['options_no_parents'] = $this->option_m->get_no_parents($this->data['content_language_id']);
        $this->data['options'] = $this->option_m->get_lang(NULL, FALSE, $this->data['content_language_id']);
        $this->data['options_nested'] = $this->option_m->get_nested($this->data['content_language_id']);
        
        //var_dump($this->data['options_nested']);
        
        // Load view
		$this->data['subview'] = 'admin/estate/options';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function edit_option($id = NULL)
	{
	    // Fetch a record or set a new one
	    if($id)
        {
            $this->data['option'] = $this->option_m->get_lang($id, FALSE, $this->data['content_language_id']);
            count($this->data['option']) || $this->data['errors'][] = 'Could not be found';
        }
        else
        {
            $this->data['option'] = $this->option_m->get_new();
        }
        
		// Options for dropdown
        $this->data['options_no_parents'] = $this->option_m->get_no_parents($this->data['content_language_id']);
        $this->data['languages'] = $this->language_m->get_form_dropdown('language');

        // Set up the form
        $rules = $this->option_m->get_all_rules();
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/estate/edit_option/'.$id);
                exit();
            }
            
            $data = $this->option_m->array_from_post($this->option_m->get_post_fields());
            if($id == NULL)
            {
                //get max order in parent id and set
                $parent_id = $this->input->post('parent_id');
                $data['order'] = $this->option_m->max_order($parent_id);
            }
            
            $data_lang = $this->option_m->array_from_post($this->option_m->get_lang_post_fields());
            $this->option_m->save_with_lang($data, $data_lang, $id);
            
            //$this->output->enable_profiler(TRUE);
            redirect('admin/estate/options');
        }
        
        // Load the view
		$this->data['subview'] = 'admin/estate/edit_option';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function update_ajax($filename = NULL)
    {
        // Save order from ajax call
        if(isset($_POST['sortable']) && $this->config->item('app_type') != 'demo')
        {
            $this->option_m->save_order($_POST['sortable']);
        }
        
        $data = array();
        $length = strlen(json_encode($data));
        header('Content-Type: application/json; charset=utf8');
        header('Content-Length: '.$length);
        echo json_encode($data);
        
        exit();
    }
    
    private function generate_sitemap()
    {
        $this->load->model('estate_m');
        $this->load->model('page_m');
        $this->load->model('option_m');
        
        $this->data['listing_uri'] = config_item('listing_uri');
        if(empty($this->data['listing_uri']))$this->data['listing_uri'] = 'property';
        
        $sitemap = $this->page_m->get_sitemap();
        $properties = $this->estate_m->get_sitemap();
        
        //For all visible languages, get options
        $langs = $this->language_m->get_array_by(array('is_frontend'=>1));
        
        $options = array();
        foreach($langs as $key=>$row_lang)
        {
            $options[$row_lang['id']] = $this->option_m->get_options($row_lang['id'], array(10));
        }
        
        $content = '';
        $content.= '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                   '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n".
                   '  	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n".
                   '  	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'."\n".
                   '			    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n";
        
        $available_langs_array = array();
        foreach($langs as $lang_code=>$lang)
        {
            $available_langs_array[] = $lang['id'];
        }
        
        foreach($sitemap as $page_obj)
        {
            if(in_array($page_obj->language_id ,$available_langs_array))
            {
                $content.= '<url>'."\n".
                        	'	<loc>'.site_url($this->language_m->get_code($page_obj->language_id).'/'.$page_obj->id.'/'.url_title_cro($page_obj->navigation_title, '-', TRUE)).'</loc>'."\n".
                        	//'	<lastmod>'.$page_obj->date.'</lastmod>'.
                        	'	<changefreq>weekly</changefreq>'."\n".
                        	'	<priority>0.5</priority>'."\n".
                        	'</url>'."\n";
            }
        }
        
        foreach($properties as $estate_obj)
        {
            foreach($langs as $lang_code=>$lang)
            {
            $content.= '<url>'."\n".
                    	'	<loc>'.site_url($this->data['listing_uri'].'/'.$estate_obj->id.'/'.$lang['code'].'/'.(isset($options[$lang['id']][$estate_obj->id][10])?url_title_cro($options[$lang['id']][$estate_obj->id][10], '-', TRUE):'')).'</loc>'."\n".
                    	//'	<lastmod>'.$page_obj->date.'</lastmod>'.
                    	'	<changefreq>weekly</changefreq>'."\n".
                    	'	<priority>0.5</priority>'."\n".
                    	'</url>'."\n";
            }
        }

        $content.= '</urlset>';
        
        $fp = fopen(FCPATH.'sitemap.xml', 'w');
        fwrite($fp, $content);
        fclose($fp);
    }
    
    public function delete_option($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/estate/options');
            exit();
        }
        
        if($this->option_m->check_deletable($id))
        {
            $this->option_m->delete($id);
        }
        else
        {
            $this->session->set_flashdata('error', 
                    lang_check('Delete disabled, child or element locked/hardlocked! But you can change or unlock it.'));
        }
		
        redirect('admin/estate/options');
	}
    
}