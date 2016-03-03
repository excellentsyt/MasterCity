<!DOCTYPE html>
<html lang="{lang_code}">
  <head>
    {template_head}
  </head>

  <body>
  
{template_header}

<div class="wrap-map" id="wrap-map">
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

{template_search}

<div class="wrap-content">
    <div class="container">

        <h2>{lang_Realestates}</h2>
        <div class="options">
            <a class="view-type active" ref="grid" href="#"><img src="assets/img/glyphicons/glyphicons_156_show_thumbnails.png" /></a>
            <a class="view-type" ref="list" href="#"><img src="assets/img/glyphicons/glyphicons_157_show_thumbnails_with_lines.png" /></a>
            
            <select class="span3 selectpicker-small pull-right" placeholder="Sort">
                <option value="date ASC">{lang_DateASC}</option>
                <option value="date DESC">{lang_DateDESC}</option>
            </select>
        </div>

        <br style="clear:both;" />

        <div class="row-fluid">
            <ul class="thumbnails">
            {has_no_results}
            <li class="span12">
            <div class="alert alert-success">
            {lang_Noestates}
            </div>
            </li>
            {/has_no_results}
            {results}
              <li class="span3">
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
                    {is_purpose_sale}
                    {has_option_36}
                    <span class="price">{option_36} {options_suffix_36}</span>
                    {/has_option_36}
                    {/is_purpose_sale}
                    {is_purpose_rent}
                    {has_option_37}
                    <span class="price">{option_37} {options_suffix_37}</span>
                    {/has_option_37}
                    {/is_purpose_rent}
                    </p>
                  </div>
                </div>
              </li>
            {/results}
            </ul>
          </div>
          <div class="pagination properties">
          {pagination_links}
          </div>
    </div>
</div>
<div class="wrap-content2">
    <div class="container">
        {page_body}
    </div>
</div>
{template_footer}
  </body>
</html>