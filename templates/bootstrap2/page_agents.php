<!DOCTYPE html>
<html lang="en">
  <head>
    {template_head}
    <script language="javascript">
    $(document).ready(function(){
        $("#wrap-map").gmap3({
         map:{
            options:{
             center: [{all_estates_center}],
             zoom: {settings_zoom},
             scrollwheel: false
            }
         },
         marker:{
            values:[
            {all_estates}
                {latLng:[{gps}], options:{icon: "{icon}"}, data:"<img style=\"width: 150px; height: 100px;\" src=\"{thumbnail_url}\" /><br />{address}<br />{option_2}<br /><span class=\"label label-info\">&nbsp;&nbsp;{option_4}&nbsp;&nbsp;</span><br /><a href=\"{url}\">{lang_Details}</a>"},
            {/all_estates}
            ],
            
        options:{
          draggable: false
        },
        events:{
          mouseover: function(marker, event, context){
            var map = $(this).gmap3("get"),
              infowindow = $(this).gmap3({get:{name:"infowindow"}});
            if (infowindow){
              infowindow.open(map, marker);
              infowindow.setContent('<div style="width:400px;display:inline;">'+context.data+'</div>');
            } else {
              $(this).gmap3({
                infowindow:{
                  anchor:marker,
                  options:{content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
                }
              });
            }
          },
          mouseout: function(){
            //var infowindow = $(this).gmap3({get:{name:"infowindow"}});
            //if (infowindow){
            //  infowindow.close();
            //}
          }
        }}});
    });    
    </script>
  </head>

  <body>
  
      <div class="container">
        <div class="masthead">
        {not_logged}
        <ul class="nav pull-right top-small">
          <li><i class="icon-phone"></i> {settings_phone}</li>
          <li><a href="mailto:{settings_email}"><i class="icon-envelope"></i> {settings_email}</a></li>
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

<div class="head-wrapper">
    <div class="container">
        <div class="row">
            <div class="masthead span12">
            <a class="logo" href="{homepage_url_lang}"><img src="assets/img/logo.png" alt="Logo" /></a>
            <div class="navbar">
              <div class="">
                <div class="container2">
                 {print_menu}
                </div>
              </div>
            </div><!-- /.navbar -->
            
            </div>  
        </div> 
            <div class="simple-languages">
                {print_lang_menu}
            </div>
    </div>  
</div>

<div class="wrap-map" id="wrap-map">
</div>

{template_search}

<div class="wrap-content">
    <div class="container container-property">
        <div class="row-fluid">
            <div class="span9">
                <h2>{page_title}</h2>
                <div class="property_content">
                {page_body}
                {has_page_images}
                <h2>{lang_Imagegallery}</h2>
                <ul data-target="#modal-gallery" data-toggle="modal-gallery" class="files files-list ui-sortable content-images">  
                    {page_images}
                    <li class="template-download fade in">
                        <a data-gallery="gallery" href="{url}" title="{filename}" download="{url}" class="preview show-icon">
                            <img src="assets/img/preview-icon.png" class="hidden" />
                        </a>
                        <div class="preview-img"><img src="{thumbnail_url}" data-src="{url}" alt="{filename}" class="" /></div>
                    </li>
                    {/page_images}
                </ul>
                
                
                <br style="clear: both;" />
                {/has_page_images}
                
                {has_page_documents}
                <h2>{lang_Filerepository}</h2>
                <ul>
                {page_documents}
                <li>
                    <a href="{url}">{filename}</a>
                </li>
                {/page_documents}
                </ul>
                {/has_page_documents}
                </div>
            </div>
            <div class="span3">
                <h2>{lang_Agents}</h2>
                {all_agents}
                <div class="agent">
                    <div class="image"><img src="{image_url}" alt="{name_surname}" /></div>
                    <div class="name">{name_surname}</div>
                    <div class="phone">{phone}</div>
                    <div class="mail"><a href="mailto:{mail}?subject={lang_Estateinqueryfor}: {page_title}">{mail}</a></div>
                </div>
                {/all_agents}
            </div>
        </div>
    </div>
</div>
    
<div class="wrap-bottom">
    <div class="container">
      <div class="row-fluid">
        <div class="span3">
            <div class="logo-transparent"></div>
            <div class="sketch-bottom">
            </div>
        </div>
        <div class="span6">
            <br />
            <table>
                <tr>
                    <td><i class="icon-map-marker icon-white"></i></td>
                    <td>
                        {settings_address_footer}
                    </td>
                </tr>
                <tr>
                    <td><i class="icon-phone icon-white"></i></td>
                    <td>{settings_phone}</td>
                </tr>
                <tr>
                    <td><i class="icon-print icon-white"></i></td>
                    <td>{settings_fax}</td>
                </tr>
                <tr>
                    <td><i class="icon-envelope icon-white"></i></td>
                    <td><a href="mailto:{settings_email}">{settings_email}</a></td>
                </tr>
            </table>
        </div>
        <div class="span3">
            <a class="developed_by" href="http://iwinter.com.hr" target="_blank"><img src="assets/img/partners/winter.png" alt="winter logo" /></a>
            
            <div class="share">
                {settings_facebook}
            </div>
            
            
        </div>
      </div>
    </div>
</div>

<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
<div id="blueimp-gallery" class="blueimp-gallery">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">&lsaquo;</a>
    <a class="next">&rsaquo;</a>
    <a class="close">&times;</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>

{settings_tracking}

  </body>
</html>
