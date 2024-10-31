function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function insertPGSASLink() {
	var tagtext;
	var add_shop = document.getElementById('add_shop_panel');
	var category = document.getElementById('pg_sas_category');
	var add_product = document.getElementById('add_product_panel');
	var add_image = document.getElementById('add_image_panel');
	var items_per_page = 0;
	var show_children = '';
	var show_header = '';
	var alignment = '';
	var height = '';
	var width = '';

	// who is active ?
	if (add_shop.className.indexOf('current') != -1) {

		items_per_page = jQuery('#pg_sas_perpage').val();
		show_children = document.getElementById('pg_sas_show_children').checked

		var category_slug = category.value;
		var tags = ['pg_sas_shop'];

		if (category_slug != 'shop-product' ) {
			tags.push("category='" + category_slug + "'");
		}
		if (show_children == false){
			tags.push("showchildren='false'");
		}
		
		if (items_per_page != '' ){
			tags.push("products_per_page='" + items_per_page + "'");
		}

		tagtext = '[' + tags.join(' ') + ']';
	}

	if (add_product.className.indexOf('current') != -1) {
		product = document.getElementById('pg_sas_product_name').value;
		show_header = document.getElementById('pg_sas_show_header').checked
		
		if ( product == 0 ){
			tinyMCEPopup.close();
		}
		
		var tags = ['pg_sas_shop'];
		
		tags.push("product_id='" + product + "'");
		
		if (show_header == false){
			tags.push("showtitle='false'");
		}

		tagtext = '[' + tags.join(' ') + ']';
	}

	if (add_image.className.indexOf('current') != -1) {

		product = document.getElementById('pg_sas_image_name').value;
		sas_align = document.getElementById('pg_sas_image_float').value;
		sas_height = document.getElementById('pg_sas_width').value;
		sas_width = document.getElementById('pg_sas_height').value;
		
		if ( product == 0 ){
			tinyMCEPopup.close();
		}
		
		var tags = ['pg_sas_image'];
		
		tags.push("product_id='" + product + "'");
		
		tags.push("float='" + sas_align + "'");
		
		if (sas_height != ''){
			tags.push("height='" + sas_height + "'");
		}
		if (sas_width != ''){
			tags.push("width='" + sas_width + "'");
		}

		tagtext = '[' + tags.join(' ') + ']';

	}

	if(window.tinyMCE) {
		//

	    /* get the TinyMCE version to account for API diffs thanks to http://stackoverflow.com/questions/22813970/typeerror-window-tinymce-execinstancecommand-is-not-a-function */
	    var tmce_ver=window.tinyMCE.majorVersion;

	    if (tmce_ver>="4") {
	        window.tinyMCE.execCommand('mceInsertContent', false, tagtext);
	    } else {
	        window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
	    }

		//Repaints the editor. Sometimes the browser has graphic glitches.
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}
