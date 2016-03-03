{has_color_picker}
<div class="top-wrapper">
      <div class="container color-picker">
        <a class="pick_orange" href="{page_current_url}?color=orange"> </a>
        <a class="pick_red" href="{page_current_url}?color=red"> </a>
        <a class="pick_green" href="{page_current_url}?color=green"> </a>
        <a class="pick_blue" href="{page_current_url}?color=blue"> </a>
        <a class="pick_purple" href="{page_current_url}?color=purple"> </a>
        <a class="pick_black" href="{page_current_url}?color=black"> </a>
      </div> <!-- /.container -->
</div>
{/has_color_picker}

<div class="top-wrapper">
      <div class="container">
        <div class="masthead">
        {not_logged}
        <ul class="nav pull-right top-small">
          <li><span><i class="icon-phone"></i> {settings_phone}</span></li>
          <li><a href="mailto:{settings_email}"><i class="icon-envelope"></i> {settings_email}</a></li>
          <li><a href="{front_login_url}"><i class="icon-user"></i> {lang_Login}</a></li>
        </ul>
        {/not_logged}
        {is_logged_user}
        <ul class="nav pull-right top-small">
          <li><a href="{myproperties_url}"><i class="icon-list"></i> {lang_Myproperties}</a></li>
          <li><a href="{logout_url}"><i class="icon-off"></i> {lang_Logout}</a></li>
        </ul>
        {/is_logged_user}
        {is_logged_other}
        <ul class="nav pull-right top-small">
          <li><a href="{login_url}"><i class="icon-wrench"></i> {lang_Admininterface}</a></li>
          <li><a href="{logout_url}"><i class="icon-off"></i> {lang_Logout}</a></li>
        </ul>
        {/is_logged_other}
        </div>
      </div> <!-- /.container -->
</div>

<div class="head-wrapper">
    <div class="container">
        <div class="row">
            <div class="span12">
                <a class="logo pull-left" href="{homepage_url_lang}"><img src="assets/img/logo.png" alt="Logo" /></a>
                <a class="logo-over pull-left" href="{homepage_url_lang}"><img src="assets/img/logo-over.png" alt="Logo" /></a>
                <div class="simple-languages pull-right">
                    {print_lang_menu}
                </div>
                <div class="navbar pull-left">
                     {print_menu}
                </div><!-- /.navbar -->
            </div>  
        </div> 
    </div>  
</div>