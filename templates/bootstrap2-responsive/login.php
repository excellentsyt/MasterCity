<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
    <script language="javascript">
    $(document).ready(function(){
        $("#wrap-map").gmap3({
         map:{
            options:{
             center: [{all_estates_center}],
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

<div class="wrap-content">
    <div class="container">
        <div class="row-fluid">
            <div class="span6 login-form">
            <h2>{lang_Login}</h2>
            <div class="property_content">
                <?php if($is_login):?>
                <?php echo validation_errors()?>
                <?php if($this->session->flashdata('error')):?>
                <p class="alert alert-error"><?php echo $this->session->flashdata('error')?></p>
                <?php endif;?>
                <?php endif;?>
                
                  <!-- Login form -->
                  <?php echo form_open(NULL, array('class' => 'form-horizontal'))?>
                    <!-- Email -->
                    <div class="control-group">
                      <label class="control-label" for="inputUsername"><?php echo lang('Username')?></label>
                      <div class="controls">
                        <?php echo form_input('username', NULL, 'class="form-control" id="inputUsername" placeholder="'.lang('Username').'"')?>
                      </div>
                    </div>
                    <!-- Password -->
                    <div class="control-group">
                      <label class="control-label" for="inputPassword"><?php echo lang('Password')?></label>
                      <div class="controls">
                        <?php echo form_password('password', NULL, 'class="form-control" id="inputPassword" placeholder="'.lang('Password').'"')?>
                      </div>
                    </div>
                    <!-- Remember me checkbox and sign in button -->
                    <div class="control-group">
					<div class="controls">
                      <div class="checkbox">
                        <label>
                          <input type="checkbox" name="remember" id="remember" value="true" /> <?php echo lang('Remember me')?>
                        </label>
						</div>
					</div>
					</div>
                    <div class="control-group">
					   <div class="controls">
							<button type="submit" class="btn btn-danger"><?php echo lang('Sign in')?></button>
							<button type="reset" class="btn btn-default"><?php echo lang('Reset')?></button>
						</div>
                    </div>
                  <?php echo form_close()?>
				  
                <?php if(config_item('app_type') == 'demo'):?>
                <p class="alert alert-info"><?php echo lang_check('User creditionals: user, user')?></p>
                <?php endif;?>
            </div></div>
            <div class="span6 register-form">
            <h2>{lang_Register}</h2>
            <div class="property_content">
                <?php if($this->session->flashdata('error_registration') != ''):?>
                <p class="alert alert-success"><?php echo $this->session->flashdata('error_registration')?></p>
                <?php endif;?>
                <?php if($is_registration):?>
                <?php echo validation_errors()?>
                <?php endif;?>
                  <!-- Login form -->
                  <?php echo form_open(NULL, array('class' => 'form-horizontal'))?>
                                <div class="control-group">
                                  <label class="control-label"><?php echo lang('FirstLast')?></label>
                                  <div class="controls">
                                    <?php echo form_input('name_surname', set_value('name_surname', ''), 'class="form-control" id="inputNameSurname" placeholder="'.lang('FirstLast').'"')?>
                                  </div>
                                </div>
                                
                                <div class="control-group">
                                  <label class="control-label"><?php echo lang('Username')?></label>
                                  <div class="controls">
                                    <?php echo form_input('username', set_value('username', ''), 'class="form-control" id="inputUsername" placeholder="'.lang('Username').'"')?>
                                  </div>
                                </div>
                                
                                <div class="control-group">
                                  <label class="control-label">Password</label>
                                  <div class="controls">
                                    <?php echo form_password('password', set_value('password', ''), 'class="form-control" id="inputPassword" placeholder="'.lang('Password').'" autocomplete="off"')?>
                                  </div>
                                </div>
                                
                                <div class="control-group">
                                  <label class="control-label"><?php echo lang('Confirmpassword')?></label>
                                  <div class="controls">
                                    <?php echo form_password('password_confirm', set_value('password_confirm', ''), 'class="form-control" id="inputPasswordConfirm" placeholder="'.lang('Confirmpassword').'" autocomplete="off"')?>
                                  </div>
                                </div>
                                
                                <div class="control-group">
                                  <label class="control-label"><?php echo lang('Address')?></label>
                                  <div class="controls">
                                    <?php echo form_textarea('address', set_value('address', ''), 'placeholder="'.lang('Address').'" rows="3" class="form-control"')?>
                                  </div>
                                </div>          
                                
                                <div class="control-group">
                                  <label class="control-label"><?php echo lang('Phone')?></label>
                                  <div class="controls">
                                    <?php echo form_input('phone', set_value('phone', ''), 'class="form-control" id="inputPhone" placeholder="'.lang('Phone').'"')?>
                                  </div>
                                </div>
                                
                                <div class="control-group">
                                  <label class="control-label"><?php echo lang('Email')?></label>
                                  <div class="controls">
                                    <?php echo form_input('mail', set_value('mail', ''), 'class="form-control" id="inputMail" placeholder="'.lang('Email').'"')?>
                                  </div>
                                </div>
                                
                    <div class="control-group">
                        <div class="controls">
    						<button type="submit" class="btn btn-danger"><?php echo lang('Register')?></button>
    						<button type="reset" class="btn btn-success"><?php echo lang('Reset')?></button>
    					</div>
                    </div>
                  <?php echo form_close()?>
            </div></div>
        </div>
        <?php if(false):?>
        <br />
        <div class="property_content">
        {page_body}
        </div>
        <?php endif;?>
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