<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    <script language="javascript">
    $(document).ready(function(){

       $("#route_from_button").click(function () { 
            window.open("https://maps.google.hr/maps?saddr="+$("#route_from").val()+"&daddr={estate_data_address}@{estate_data_gps}&hl={lang_code}",'_blank');
            return false;
        });

        $('#propertyLocation').gmap3({
         map:{
            options:{
             center: [{estate_data_gps}],
             zoom: {settings_zoom},
             scrollwheel: false
            }
         },
         marker:{
            values:[
                {latLng:[{estate_data_gps}], options:{icon: "{estate_data_icon}"}, data:"{estate_data_address}<br />{lang_GPS}: {estate_data_gps}"},
            ],
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
                  options:{disableAutoPan: mapDisableAutoPan, content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
                }
              });
            }
          }
        }
         }});
        
        $("#wrap-map").gmap3({
         map:{
            options:{
             center: [{estate_data_gps}],
             zoom: {settings_zoom},
             scrollwheel: false,
             mapTypeId: c_mapTypeId,
             mapTypeControlOptions: {
               mapTypeIds: c_mapTypeIds
             }
            }
         },
        styledmaptype:{
          id: "style1",
          options:{
            name: "<?php echo lang_check('CustomMap'); ?>"
          },
          styles: mapStyle
        },
         marker:{
            values:[
            {all_estates}
                {latLng:[{gps}], options:{icon: "{icon}"}, data:"<img style=\"width: 150px; height: 100px;\" src=\"{thumbnail_url}\" /><br />{address}<br />{option_2}<br /><span class=\"label label-info\">&nbsp;&nbsp;{option_4}&nbsp;&nbsp;</span><br /><a href=\"{url}\">{lang_Details}</a>"},
            {/all_estates}
            ],
            cluster: clusterConfig,
            options: markerOptions,
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
                  options:{disableAutoPan: mapDisableAutoPan, content: '<div style="width:400px;display:inline;">'+context.data+'</div>'}
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
        init_gmap_searchbox();
    });    
    </script>
  </head>

  <body>
{template_header}

<input id="pac-input" class="controls" type="text" placeholder="{lang_Search}" />
<div class="wrap-map" id="wrap-map">
</div>

{template_search}

<?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
{has_ads_728x90px}
<div class="wrap-content2">
    <div class="container ads">
        <a href="{random_ads_728x90px_link}" target="_blank"><img src="{random_ads_728x90px_image}" /></a>
    </div>
</div>
{/has_ads_728x90px}
<?php endif;?>

<div class="wrap-content">
    <div class="container container-property">
        <div class="row-fluid">
            <div class="span9">
            <h2>{page_title}</h2>
            {has_page_images}
            <div class="propertyCarousel">
                <div id="myCarousel" class="carousel slide">
                <ol class="carousel-indicators">
                {slideshow_images}
                <li data-target="#myCarousel" data-slide-to="{num}" class="{first_active}"></li>
                {/slideshow_images}
                </ol>
                <!-- Carousel items -->
                <div class="carousel-inner">
                {slideshow_images}
                    <div class="item {first_active}">
                    <img alt="" src="{url}" />
                    </div>
                {/slideshow_images}
                </div>
                <!-- Carousel nav -->
                <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
                <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
                </div>
            </div>
            {/has_page_images}
              <div class="property_content">
                <h2>{lang_Description}</h2>
                {page_body}
                <h2>{lang_Generalamenities}</h2>
                <ul class="amenities">
                {category_options_21}
                {is_checkbox}
                <li>
                <img src="assets/img/checkbox_{option_value}.png" alt="{option_value}" class="check" />&nbsp;&nbsp;{option_name}&nbsp;&nbsp;{icon}
                </li>
                {/is_checkbox}
                {/category_options_21}
                </ul>
                <br style="clear: both;" />
                <h2>{lang_Propertylocation}</h2>
                <div id="propertyLocation">
                </div>
                <div class="route_suggestion">
                <input id="route_from" class="inputtext w360" type="text" value="" placeholder="{lang_Typeaddress}" name="route_from" />
                <a id="route_from_button" href="#" class="btn">{lang_Suggestroutes}</a>
                </div>
                
                <?php if(!empty($estate_data_option_12)): ?>
                <h2>{options_name_9}</h2>
                {estate_data_option_12}
                <?php endif; ?>
                
                {has_page_images}
                <h2>{lang_Imagegallery}</h2>
                <ul data-target="#modal-gallery" data-toggle="modal-gallery" class="files files-list ui-sortable">  
                    {page_images}
                    <li class="template-download fade in">
                        <a data-gallery="gallery" href="{url}" title="{filename}" download="{url}" class="preview show-icon">
                            <img src="assets/img/preview-icon.png" class="" />
                        </a>
                        <div class="preview-img"><img src="{thumbnail_url}" data-src="{url}" alt="{filename}" class="" /></div>
                    </li>
                    {/page_images}
                </ul>
                {/has_page_images}
                <br style="clear:both;" />
                
                <h2>{lang_Agentestates}</h2>
                <ul class="thumbnails agent-property">
                {agent_estates}
                      <li class="span4">
                        <div class="thumbnail">
                          <h3>{option_10}&nbsp;</h3>
                          <img alt="300x200" data-src="holder.js/300x200" style="width: 300px; height: 200px;" src="{thumbnail_url}" />
                          {has_option_38}
                          <div class="badget"><img src="assets/img/badgets/{option_38}.png" alt="{option_38}"/></div>
                          {/has_option_38}
                          <a href="{url}" class="over-image"> </a>
                          <div class="caption">
                            <p class="bottom-border"><strong>{address}</strong></p>
                            <p class="bottom-border">{options_name_2} <span>{option_2}</span></p>
                            <p class="bottom-border">{options_name_3} <span>{option_3}</span></p>
                            <p class="bottom-border">{options_name_19} <span>{option_19}</span></p>
                            <p class="prop-description"><i>{option_chlimit_8}</i></p>
                            <p>
                            <a class="btn btn-info" href="{url}">
                            {lang_Details}
                            </a>
                            {has_option_36}
                            <span class="price">{option_36} {options_suffix_36}</span>
                            {/has_option_36}
                            </p>
                          </div>
                        </div>
                      </li>
                {/agent_estates}
                </ul>
                <br style="clear:both;" />

              </div>
            </div>
            <div class="span3">
                  <h2>{lang_Overview}</h2>
                  <div class="property_options">
                    <p class="bottom-border"><strong>
                    {lang_Address}
                    </strong> <span>{estate_data_address}</span>
                    <br style="clear: both;" />
                    </p>
                    {category_options_1}
                    {is_text}
                    <p class="bottom-border"><strong>{option_name}:</strong> <span>{option_value} {option_suffix}</span></p>
                    {/is_text}
                    {is_dropdown}
                    <p class="bottom-border"><strong>{option_name}:</strong> <span class="label label-success">&nbsp;&nbsp;{option_value}&nbsp;&nbsp;</span></p>
                    {/is_dropdown}
                    {is_checkbox}
                    <img src="assets/img/checkbox_{option_value}.png" alt="{option_value}" />&nbsp;&nbsp;{option_name}
                    {/is_checkbox}
                    {/category_options_1}
                  </div>
                  <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
                    {has_ads_160x600px}
                    <h2>{lang_Ads}</h2>
                    <div class="sidebar-ads-1">
                        <a href="{random_ads_160x600px_link}" target="_blank"><img src="{random_ads_160x600px_image}" /></a>
                    </div>
                    {/has_ads_160x600px}
                  <?php endif;?>
                  
                  {has_agent}
                  <h2>{lang_Agent}</h2>
                  <div class="agent">
                    <div class="image"><img src="{agent_image_url}" alt="{agent_name_surname}" /></div>
                    <div class="name">{agent_name_surname}</div>
                    <div class="phone">{agent_phone}</div>
                    <div class="mail"><a href="mailto:{agent_mail}?subject={lang_Estateinqueryfor}: {estate_data_id}, {page_title}">{agent_mail}</a></div>
                  </div>
                  {/has_agent}

                  <h2>{lang_Enquireform}</h2>
                  <div id="form" class="property-form">
                    {validation_errors}
                    {form_sent_message}
                    <form method="post" action="{page_current_url}#form">
                        <label>{lang_FirstLast}</label>
                        <input class="{form_error_firstname}" name="firstname" type="text" placeholder="{lang_FirstLast}" value="{form_value_firstname}" />
                        <label>{lang_Phone}</label>
                        <input class="{form_error_phone}" name="phone" type="text" placeholder="{lang_Phone}" value="{form_value_phone}" />
                        <label>{lang_Email}</label>
                        <input class="{form_error_email}" name="email" type="text" placeholder="{lang_Email}" value="{form_value_email}" />
                        <label>{lang_Address}</label>
                        <input class="{form_error_address}" name="address" type="text" placeholder="{lang_Address}" value="{form_value_address}" />
                        {is_purpose_rent}
                        <label>{lang_FromDate}</label>
                        <input name="fromdate" type="text" id="datetimepicker1" value="{form_value_fromdate}" class="{form_error_fromdate}" placeholder="{lang_FromDate}" />
                        <label>{lang_ToDate}</label>
                        <input class="{form_error_todate}" id="datetimepicker2" name="todate" type="text" placeholder="{lang_ToDate}" value="{form_value_todate}" />
                        {/is_purpose_rent}
                        <label>{lang_Message}</label>
                        <textarea class="{form_error_message}" name="message" rows="3" placeholder="{lang_Message}">{form_value_message}</textarea>
                        <br style="clear: both;" />
                        <p style="text-align:right;">
                        <button type="submit" class="btn btn-info">{lang_Send}</button>
                        </p>
                    </form>
                  </div>
                  <?php if(file_exists(APPPATH.'controllers/admin/ads.php')):?>
                    {has_ads_180x150px}
                    <h2>{lang_Ads}</h2>
                    <div class="sidebar-ads-1">
                        <a href="{random_ads_180x150px_link}" target="_blank"><img src="{random_ads_180x150px_image}" /></a>
                    </div>
                    {/has_ads_180x150px}
                  <?php endif;?>
                  
                  
            </div>
        </div>
    </div>
</div>
    
{template_footer}

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

  </body>
</html>