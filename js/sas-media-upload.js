jQuery(document).ready(function() {
	    	jQuery('#pgeek_sas_image_button').click(function() {
	    		original_send_to_editor = window.send_to_editor;
	    		post_id = jQuery(this).attr('rel');
	    		window.send_to_editor = function(html) {
	    			 imgurl = jQuery('img',html).attr('src');
	    			 jQuery('#pgeek_sas_image').val(imgurl);
	    			 tb_remove();
	    			 window.send_to_editor = original_send_to_editor;
	    			}
	    			 tb_show('', 'media-upload.php?post_id='+post_id+'&amp;type=image&amp;TB_iframe=true');
	    			 return false;
	    		});
	    });