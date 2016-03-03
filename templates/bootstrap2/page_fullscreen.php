<!DOCTYPE html>
<html lang="en">
  <head>
    {template_head}
    <script language="javascript">
    
    function resize_map()
    {
        var map_height = $(window).height()-
                $('.top-wrapper').height()-
                $('.head-wrapper').height()-
                $('.wrap-search').height();
        
        $("#wrap-map").height(map_height);
    }
    
    $(window).load(function() {
        
        resize_map();
        $(window).on('resize', function(){
              resize_map();
        });
        
    //$(document).ready(function(){
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
                {latLng:[{gps}], options:{ icon: "{icon}"}, data:"<img style=\"width: 150px; height: 100px;\" src=\"{thumbnail_url}\" /><br />{address}<br />{option_2}<br /><span class=\"label label-info\">&nbsp;&nbsp;{option_4}&nbsp;&nbsp;</span><br /><a href=\"{url}\">{lang_Details}</a>"},
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
          }
//        ,mouseout: function(){
//            var infowindow = $(this).gmap3({get:{name:"infowindow"}});
//            if (infowindow){
//              infowindow.close();
//            }
//          }
        }}});
    });
    
    </script>
  </head>

  <body>
<div class="top-wrapper">
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
</div>
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
{settings_tracking}
    
  </body>
</html>