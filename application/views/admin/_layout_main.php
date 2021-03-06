<?php $this->load->view('admin/components/page_head_main')?>
<body>
<div class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
  
    <div class="containerk">
      <!-- Menu button for smallar screens -->
		<div class="navbar-header">
		  <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		  <a href="<?php echo site_url('admin/dashboard')?>" class="navbar-brand"><img src="<?php echo base_url('admin-assets/img/custom/logo-system-mini.png');?>" />Real estate <span class="bold">point</span></a>
		</div>
      <!-- Site name for smallar screens -->

      <!-- Navigation starts -->
      <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">     

        <!-- Links -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">            
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
              <?php if($this->session->userdata('profile_image') != ''):?><img src="<?php echo base_url($this->session->userdata('profile_image'));?>" alt="" class="nav-user-pic img-responsive" /> <?php endif;?><?php echo $this->session->userdata('name_surname')?> <b class="caret"></b>              
            </a>
            
            <!-- Dropdown menu -->
            <ul class="dropdown-menu">
              <li><a href="<?php echo site_url('admin/user/edit/'.$this->session->userdata('id'))?>"><i class="icon-user"></i> <?php echo lang('Profile');?></a></li>
              <?php if(check_acl('settings')):?><li><a href="<?php echo site_url('admin/settings')?>"><i class="icon-cogs"></i> <?php echo lang('Settings');?></a></li><?php endif;?>
              <li><a href="<?php echo site_url('admin/user/logout')?>"><i class="icon-off"></i> <?php echo lang('Logout');?></a></li>
            </ul>
          </li>
          
        </ul>

        <!-- Notifications -->
        <ul class="nav navbar-nav navbar-right">
            
            <?php if(check_acl('enquire')):?>
            <!-- Message button with number of latest messages count-->
            <li class="dropdown dropdown-big">
              <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="icon-envelope-alt"></i> <?php echo lang('Enquires');?> <span class="badge badge-important"><?php echo $this->enquire_m->total_unreaded();?></span> 
              </a>

                <ul class="dropdown-menu">
                  <li>
                    <!-- Heading - h5 -->
                    <h5><i class="icon-envelope-alt"></i> <?php echo lang('Enquires');?></h5>
                    <!-- Use hr tag to add border -->
                    <hr />
                  </li>
                    <?php foreach($enquire_3 as $enquire):?>
                  <li>
                    <!-- List item heading h6 -->
                    <a href="<?php echo site_url('admin/enquire/edit/'.$enquire->id)?>"><?php echo $enquire->name_surname?></a>
                    <!-- List item para -->
                    <p><?php echo word_limiter(strip_tags($enquire->message), 9);?></p>
                    <hr />
                  </li>
                    <?php endforeach;?>    
                  <li>
                    <div class="drop-foot">
                      <a href="<?php echo site_url('admin/enquire')?>"><?php echo lang('View All');?></a>
                    </div>
                  </li>                                    
                </ul>
            </li>
            <?php endif;?>
            
            <?php if(check_acl('user')):?>
            <!-- Members button with number of latest members count -->
            <li class="dropdown dropdown-big">
              <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="icon-user"></i> <?php echo lang('Users');?> <span   class="badge badge-warning"><?php echo $this->user_m->total_unactivated();?></span> 
              </a>

                <ul class="dropdown-menu">
                  <li>
                    <!-- Heading - h5 -->
                    <h5><i class="icon-user"></i> <?php echo lang('Users');?></h5>
                    <!-- Use hr tag to add border -->
                    <hr />
                  </li>
                    <?php foreach($users_3 as $user):?>
                  <li>
                    <!-- List item heading h6-->
                    <a href="<?php echo site_url('admin/user/edit/'.$user->id)?>"><?php echo $user->name_surname?></a> 
                    <span class="label label-<?php echo $this->user_m->user_type_color[$user->type]?> pull-right"><?php echo $this->user_m->user_types[$user->type]?></span>
                    <div class="clearfix"></div>
                    <hr />
                  </li>
                    <?php endforeach;?>               
                  <li>
                    <div class="drop-foot">
                      <a href="<?php echo site_url('admin/user')?>"><?php echo lang('View All');?></a>
                    </div>
                  </li>                                    
                </ul>
            </li>
            <?php endif;?>

        </ul>
		</nav>
      </div>

    </div>
  



<!-- Main content starts -->

<div class="content">

  	<!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-dropdown"><a href="#">Navigation</a></div>

        <div class="sidebar-inner">

          <!-- Search form -->
          <div class="sidebar-widget">
             <?php echo form_open('admin/dashboard/search');?>
              	<input type="text" class="form-control" name="search" placeholder="<?php echo lang('Search')?>" />
            <?php echo form_close();?>
          </div>

          <!--- Sidebar navigation -->
          <!-- If the main navigation has sub navigation, then add the class "has_submenu" to "li" of main navigation. -->
          <ul class="navi">

            <!-- Use the class nred, ngreen, nblue, nlightblue, nviolet or norange to add background color. You need to use this in <li> tag. -->

            <li class="nred<?php echo (strpos($this->uri->uri_string(),'dashboard')!==FALSE || $this->uri->uri_string() == 'admin')?' current':'';?>"><a href="<?php echo site_url('admin/dashboard')?>"><i class="icon-desktop"></i> <?php echo lang('Dashboard');?></a></li>
            
            <?php if(check_acl('page')):?>
            <li class="ngreen<?php echo (strpos($this->uri->uri_string(),'page')!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/page')?>"><i class="icon-sitemap"></i> <?php echo lang('Pages');?></a></li>
            <?php endif;?>
            
            <!-- Menu with sub menu -->
            <li class="has_submenu nlightblue<?php echo (strpos($this->uri->uri_string(),'estate')!==FALSE)?' current open':'';?>">
              <a href="#">
                <!-- Menu name with icon -->
                <i class="icon-map-marker"></i> <?php echo lang('Estates');?> 
                <!-- Icon for dropdown -->
                <span class="pull-right"><i class="icon-angle-right"></i></span>
              </a>

              <ul>
                <li><a href="<?php echo site_url('admin/estate')?>"><?php echo lang('Manage');?></a></li>
                <?php if(check_acl('estate/options')):?>
                <li><a href="<?php echo site_url('admin/estate/options')?>"><?php echo lang('Options');?></a></li>
                <?php endif;?>
              </ul>
            </li>
            
            <?php if(check_acl('slideshow')):?>
            <li class="norange<?php echo (strpos($this->uri->uri_string(),'slideshow')!==FALSE)?' current':'';?>"><a href="<?php echo site_url('admin/slideshow')?>"><i class="icon-picture"></i> <?php echo lang('Slideshow')?></a></li>
            <li class="nviolet<?php echo (strpos($this->uri->uri_string(),'statistics')!==FALSE)?' current':'';?>"><a target="_blank" href="https://www.google.com/analytics/web"><i class="icon-bar-chart"></i> <?php echo lang('Statistics');?></a></li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/news.php') && check_acl('news')):?>
            <li class="has_submenu nblue<?php echo (strpos($this->uri->uri_string(),'news')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-book"></i> <?php echo lang_check('News');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/news')?>"><?php echo lang('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/news/categories')?>"><?php echo lang_check('Categories');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/ads.php') && check_acl('ads')):?>
            <li class="nred<?php echo (strpos($this->uri->uri_string(),'ads')!==FALSE)?' current open':'';?>">
                <a href="<?php echo site_url('admin/ads')?>">
                    <!-- Menu name with icon -->
                    <i class="icon-globe"></i> <?php echo lang_check('Ads');?>
                </a>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/showroom.php') && check_acl('showroom')):?>
            <li class="has_submenu ngreen<?php echo (strpos($this->uri->uri_string(),'showroom')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-briefcase"></i> <?php echo lang_check('Showroom');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/showroom')?>"><?php echo lang('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/showroom/categories')?>"><?php echo lang_check('Categories');?></a></li>
              </ul>
            </li>
            <?php endif;?>
            
            <?php if(file_exists(APPPATH.'controllers/admin/expert.php') && check_acl('expert')):?>
            <li class="has_submenu nlightblue<?php echo (strpos($this->uri->uri_string(),'expert')!==FALSE)?' current open':'';?>">
                <a href="#">
                    <!-- Menu name with icon -->
                    <i class="icon-comment"></i> <?php echo lang_check('Q&A');?>
                    <!-- Icon for dropdown -->
                    <span class="pull-right"><i class="icon-angle-right"></i></span>
                </a>
              <ul>
                <li><a href="<?php echo site_url('admin/expert')?>"><?php echo lang('Manage');?></a></li>
                <li><a href="<?php echo site_url('admin/expert/categories')?>"><?php echo lang_check('Categories');?></a></li>
              </ul>
            </li>
            <?php endif;?>

          </ul>
  
          <?php if(false):?>
          <!-- Date -->
          <div class="sidebar-widget">
            <div id="todaydate"></div>
          </div>
          <?php endif;?>

        </div>

    </div>

    <!-- Sidebar ends -->

  	<!-- Main bar -->
  	<div class="mainbar">
    <?php $this->load->view($subview)?>
    </div>
</div>
<!-- Content ends -->

<?php $this->load->view('admin/components/page_tail_main')?>