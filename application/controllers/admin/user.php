<?php

class User extends Admin_Controller 
{

	public function __construct(){
		parent::__construct();
        
        $this->load->model('file_m');
        $this->load->model('repository_m');
        $this->load->model('qa_m');
        
        // Get language for content id to show in administration
        $this->data['content_language_id'] = $this->language_m->get_content_lang();
	}
    
    public function index($pagination_offset=0)
	{
	    $this->load->library('pagination');
       
	    // Fetch all users
		$this->data['users'] = $this->user_m->get();
        
        // pagination
        $config['base_url'] = site_url('admin/user/index');
        $config['uri_segment'] = 4;
        $config['total_rows'] = count($this->data['users']);
        $config['per_page'] = 20;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        
        $this->data['users'] = $this->user_m->get_pagination($config['per_page'], $pagination_offset);
        
        $this->data['expert_categories'] = $this->qa_m->get_no_parents_expert($this->data['content_language_id']);
        
        // Load view
		$this->data['subview'] = 'admin/user/index';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function export()
    {
        $this->load->helper('download');
        
	    // Fetch all users
		$users = $this->user_m->get();
        
        $data = '';
        
        foreach($users as $row)
        {
            if(strpos($row->mail, '@') > 1)
            {
                $data.= $row->mail."\r\n";
            }
        }
        
        if(strlen($data) > 2)
            $data = substr($data,0,-1);
        
        $name = 'real-estate-users.txt';
        
        force_download($name, $data); 
    }
    
    public function edit($id = NULL)
	{
	    // Fetch a user or set a new one
	    if($id)
        {
            $this->data['user'] = $this->user_m->get($id);
            
            if(count($this->data['user']) == 0)
            {
                $this->data['errors'][] = 'User could not be found';
                redirect('admin/user');
            }
            
            //Check if user have permissions
            if($this->session->userdata('type') != 'ADMIN')
            {
                if($id == $this->session->userdata('id'))
                {
                    
                }
                else
                {
                    redirect('admin/user');
                }
            }
            
            // Fetch file repository
            $repository_id = $this->data['user']->repository_id;
            if(empty($repository_id))
            {
                // Create repository
                $repository_id = $this->repository_m->save(array('name'=>'user_m'));
                
                // Update page with new repository_id
                $this->user_m->save(array('repository_id'=>$repository_id), $this->data['user']->id);
            }
        }
        else
        {
            $this->data['user'] = $this->user_m->get_new();
        }
       
		$id == NULL || $this->data['user'] = $this->user_m->get($id);
        
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
        
        
        $this->data['expert_categories'] = $this->qa_m->get_no_parents_expert($this->data['content_language_id']);
        
        // Set up the form
        $rules = $this->user_m->rules_admin;
        $id || $rules['password']['rules'] .= '|required';

        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/edit/'.$id);
                exit();
            }
            
            $data = $this->user_m->array_from_post(array('name_surname', 'mail', 'password', 'username',
                                                         'address', 'description', 'mail', 'phone', 'type', 'qa_id', 'language', 'activated'));
            if($data['password'] == '')
            {
                unset($data['password']);
            }
            else
            {
                $data['password'] = $this->user_m->hash($data['password']);
            }
            
            if($this->session->userdata('type') != 'ADMIN')
                unset($data['type']);
            
            if($id == NULL)
                $data['registration_date'] = date('Y-m-d H:i:s');
            
            $id = $this->user_m->save($data, $id);
            
            redirect('admin/user/edit/'.$id);
        }
        
        // Load the view
		$this->data['subview'] = 'admin/user/edit';
        $this->load->view('admin/_layout_main', $this->data);
	}
    
    public function all_deactivate($user_id)
    {
        $this->load->model('estate_m');
        
        //Get user properties
        $user_properties = $this->estate_m->get_user_properties($user_id);
        
        //Activate/deactivate all user properties
        $this->estate_m->change_activated_properties($user_properties, 0);
        
        //Set message
        $this->session->set_flashdata('error', 
                        lang_check('All properties from specific user is deactivated!').' ('.$user_id.')');
        
        redirect('admin/user/');
    }
    
    public function all_activate($user_id)
    {
        $this->load->model('estate_m');
        
        //Get user properties
        $user_properties = $this->estate_m->get_user_properties($user_id);
        
        //Activate/deactivate all user properties
        $this->estate_m->change_activated_properties($user_properties, 1);
        
        //Set message
        $this->session->set_flashdata('error', 
                        lang_check('All properties from specific user is activated!').' ('.$user_id.')');
        
        redirect('admin/user/');
    }
    
    public function delete($id)
	{
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('admin/user');
            exit();
        }
       
		$this->user_m->delete($id);
        redirect('admin/user');
	}
    
    //public function login_secret()
    public function login()
	{
	    // Redirect a user if he's alredy logged in'
        
        
	    $dashboard = 'admin/dashboard';
        
        if($this->user_m->loggedin() === TRUE)
        {
            if($this->session->userdata('type') == 'USER')
            {
                redirect('frontend/login', 'refresh');
            }
            else
            {
                redirect($dashboard, 'refresh');
            }
        }
        
        // Set form
        $rules = $this->user_m->rules;
        $this->form_validation->set_rules($rules);
        
        // Process form
        if($this->form_validation->run() == TRUE)
        {
            // We can login and redirect
            if($this->user_m->login() == TRUE)
            {
                redirect($dashboard);
            }
            else
            {
                $this->session->set_flashdata('error', 
                        lang('That email/password combination does not exists or account not activated'));
                redirect('admin/user/login', 'refresh');                
            }
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/login';
        $this->load->view('admin/_layout_modal', $this->data);
	}
    
    public function register()
	{
	    // Redirect a user if he's alredy logged in'
	    $dashboard = 'admin/dashboard';
	    $this->user_m->loggedin() == FALSE || redirect($dashboard);
        
	    // Set a new user
        $this->data['user'] = $this->user_m->get_new();
        
        // Set up the form
        $rules = $this->user_m->rules_admin;
        $rules['password']['rules'] .= '|required';
        $rules['type']['rules'] = 'trim';
        
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error', 
                        lang('Data editing disabled in demo'));
                redirect('admin/user/register');
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
            $data['registration_date'] = date('Y-m-d H:i:s');
            
            $this->user_m->save($data, NULL);
            
            $this->session->set_flashdata('error', 
                    lang('Thanks on registration, please wait account activation'));
            redirect('admin/user/login', 'refresh');
        }
        
        // Load view
		$this->data['subview'] = 'admin/user/register';
        $this->load->view('admin/_layout_modal', $this->data);
	}
    
    public function logout()
    {
        $this->user_m->logout();
        redirect('admin/user/login');
    }
    
    public function _unique_username($str)
    {
        // Do NOT validate if email alredy exists
        // UNLESS it's the email for the current user
        
        $id = $this->uri->segment(4);
        $this->db->where('username', $this->input->post('username'));
        !$id || $this->db->where('id !=', $id);
        
        $user = $this->user_m->get();
        
        if(count($user))
        {
            $this->form_validation->set_message('_unique_username', '%s '.lang('should be unique'));
            return FALSE;
        }
        
        return TRUE;
    }
    
}