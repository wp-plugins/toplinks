/**
 * TopLinks Wordpress plugin
 * Copyright (c) 2008 76design/Thornley Fallis Communications
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/

jQuery.noConflict();

jQuery(document).ready(function() {
  var currentPage = 0;
  var maxPage = 0;

  jQuery.ajax({
  		type: "GET",
  		url: toplinks_url + "/wp-content/plugins/toplinks/toplinks.php",
  		data: "tl_toplinksmax=1",
  		success: function(data) {
  			maxPage = data;
  		}
  	});
  
  jQuery('#tl_next_page').bind('click', function(){
    jQuery("#tl_toplinkslist").slideUp();
  	jQuery.ajax({
  		type: "GET",
  		url: toplinks_url + "/wp-content/plugins/toplinks/toplinks.php",
  		data: "tl_getpagetoplinks=" + ++currentPage,
  		success: function(data) {
  			if(currentPage == (maxPage-1)){
  				jQuery('#tl_next_page').hide();
  				jQuery('#tl_prev_page').show();
  			}
  			if(currentPage > 0){
  				jQuery('#tl_prev_page').show();
  			}
  			jQuery("#tl_toplinkslist").html(data).slideDown();
  			setTimeout(check_all_images, 2000);
  		}
  	});

  	return false;
  });	
  
  jQuery('#tl_prev_page').bind('click', function(){
    jQuery("#tl_toplinkslist").slideUp();
  	jQuery.ajax({
  		type: "GET",
  		url: toplinks_url + "/wp-content/plugins/toplinks/toplinks.php",
  		data: "tl_getpagetoplinks=" + --currentPage,
  		success: function(data) {
  			if(currentPage <= 0){
  				jQuery('#tl_prev_page').hide();
  				jQuery('#tl_next_page').show();
  			}
  			if(currentPage < maxPage-1){
  				jQuery('#tl_next_page').show();
  			}
  			jQuery("#tl_toplinkslist").html(data).slideDown();
  			setTimeout(check_all_images, 2000);
  		}
  	});
  	return false;
  });	
	
  jQuery('#tl_show_all').bind('click', function() {
  	jQuery("#tl_toplinkslist").slideUp();
  	jQuery.ajax({
  		type: "GET",
  		url: toplinks_url + "/wp-content/plugins/toplinks/toplinks.php",
  		data: "tl_getalltoplinks=1",
  		success: function(data) {
  			jQuery("#tl_toplinkslist").html(data).slideDown();
  			setTimeout(check_all_images, 2000);
  		}
  	});
  	
  	return false;
  });
  
  function check_all_images() {
	jQuery('#toplinks OL LI IMG').each(function() {
		if (!IsImageOk(this)) {
			jQuery(this).attr('src', toplinks_url + "/wp-content/plugins/toplinks/images/default_favicon.gif");
		}
	});
  }

  setTimeout(check_all_images, 2000);
})

function IsImageOk(img) {
    // During the onload event, IE correctly identifies any images that
    // weren't downloaded as not complete. Others should too. Gecko-based
    // browsers act like NS4 in that they report this incorrectly.
    if (!img.complete) {
        return false;
    }

    // However, they do have two very useful properties: naturalWidth and
    // naturalHeight. These give the true size of the image. If it failed
    // to load, either of these should be zero.
    if (typeof img.naturalWidth != "undefined" && img.naturalWidth == 0) {
        return false;
    }

    // No other way of checking: assume it's ok.
    return true;
}