<?php
$is_legacy = version_compare( get_bloginfo( 'version' ), '4.8', '<' );
$categorylist = get_terms('pg_sas_type',array('hide_empty'=> 0));
$allProducts = get_posts('post_type=pgeek_sas&nopaging=true');


//Check capabilities
if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') )
	wp_die( __( 'You don\'t have permission to be doing that!', 'wpsc' ) );
	
function pg_sas_check_title($title){
	if ('' == $title )
		$title = '(blank product title)';
		
	return esc_attr($title);
}


?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Simple Affiliate Shop</title>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/jquery.js"></script>
        <?php if ( ! $is_legacy ): ?>
        <script language="javascript" type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>/fix.js"></script>
        <?php endif; ?>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/mctabs.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo plugin_dir_url(__FILE__); ?>pg_sas_tinymce.js"></script>

		<base target="_self" />
		<style type='text/css'>
			#link .panel_wrapper, #link div.current { height:150px;}
			div.current{
				overflow-y: auto !important;
				
			}
			.current table tbody{
				font-size: 11px;
			}

			.description{
				color:grey	!important;
				font-style: italic !important;
				font-size: 10px;
			}

			#product_slider_panel a{
				color: blue	!important;
			}
		</style>
	</head>

	<body id="link" onload="tinyMCEPopup.executeOnLoad('init();'); document.body.style.display=''; mcTabs.displayTab('add_shop','add_shop_panel');" style="display:none;">
		<form name="pg_sas_button" action="#">
			<div class="tabs">
				<ul>
					<li id="add_shop" class="current"><span><a href="javascript:mcTabs.displayTab('add_shop','add_shop_panel');" onmousedown="return false;"><?php _e("Shop", 'pg-sas'); ?></a></span></li>
					<li id="add_product"><span><a href="javascript:mcTabs.displayTab('add_product','add_product_panel');" onmousedown="return false;"><?php _e("Product", 'pg-sas'); ?></a></span></li>
					<li id="add_image"><span><a href="javascript:mcTabs.displayTab('add_image','add_image_panel');" onmousedown="return false;"><?php _e("Inline image", 'pg-sas'); ?></a></span></li>
				</ul>
			</div>
	
			<div class="panel_wrapper">
				<!-- 	Add Shop shortcode options -->
				<div id="add_shop_panel" class="panel current"><br />
					<table border="0" cellpadding="4" cellspacing="0">
						<tr valign="top">
							<td><strong><label for="pg_sas_category"><?php _e("Select Product Type: ", 'pg-sas'); ?></label></strong></td>
							<td>
								<select id="pg_sas_category" name="pg_sas_category" style="width: 150px">
									<?php
										foreach($categorylist as $category){
											$selected = ( $category->slug == 'shop-product')?' selected="true"':'';
											$default =  ( $category->slug == 'shop-product')?' (default)':'';
											echo "<option value=".$category->slug.$selected." >".$category->name.$default."</option>"."\n";
										}
									?>
								</select><br />
								<span class="description"><?php _e('Limit shop to a category.', 'pg-sas') ?></span>
							</td>
						</tr>
		
						<tr valign="top">
							<td><strong><label for="pg_sas_perpage"><?php _e("Products per Page: ", 'pg-sas'); ?></label></strong></td>
							<td>
								<input name="number_per_page" id="pg_sas_perpage" type="text" value="" style="width: 80px" /><br />
								<span class="description"><?php _e('Limit products displayed per page.', 'pg-sas') ?></span>
							</td>
						</tr>
		
						<tr valign="top">
							<td><strong><label for="pg_sas_show_children"><?php _e("Show Child Categories:", 'wpsc'); ?></label></strong></td>
							<td>
								<input type="checkbox" id="pg_sas_show_children" name="pg_sas_show_children" value="1" checked="checked">
								<br /><span class="description"><?php _e('Display children of this category' , 'pg-sas') ?></span>
							</td>
						</tr>
					</table>
				</div>
		
				<!-- 	Add Product shortcode options -->
				<div id="add_product_panel" class="panel"><br />
					<table border="0" cellpadding="4" cellspacing="0">
						<tr valign="top">
							<td><strong><label for="pg_sas_product_name"><?php _e("Select a Product", 'pg-sas'); ?></label></strong></td>
							<td>
								<select id="pg_sas_product_name" name="pg_sas_product_name" style="width: 200px">
									<option value="0"><?php _e("No Product", 'pg-sas'); ?></option>
									<?php
										foreach($allProducts as $product)
											echo "<option value=".$product->ID." >".pg_sas_check_title( $product->post_title )."</option>"."\n";
									?>
								</select><br />
								<span class="description"><?php _e('Select the product you would like to create a shortcode for.', 'pg-sas') ?></span>
							</td>
						</tr>
						<tr valign="top">
							<td><strong><label for="pg_sas_show_header"><?php _e("Show Header:", 'wpsc'); ?></label></strong></td>
							<td>
								<input type="checkbox" id="pg_sas_show_header" name="pg_sas_show_header" value="1" checked="checked">
								<br /><span class="description"><?php _e('Show the header of the product above the other details' , 'pg-sas') ?></span>
							</td>
						</tr>
					</table>
				</div>
		
				<!-- 	Add Inline Image shortcode options -->
				<div id="add_image_panel" class="panel"><br />
					<table border="0" cellpadding="4" cellspacing="0">
		
						<tr valign="top">
							<td><strong><label for="pg_sas_image_name"><?php _e("Select Image", 'pg-sas'); ?></label></strong></td>
							<td>
								<select id="pg_sas_image_name" name="pg_sas_image_name" style="width: 200px">
									<option value="0"><?php _e("No Product", 'pg-sas'); ?></option>
									<?php
										foreach($allProducts as $product)
											echo "<option value=".$product->ID." >".pg_sas_check_title( $product->post_title )."</option>"."\n";
									?>
								</select><br />
								<span class="description"><?php _e('Take the image from this product.', 'pg-sas') ?></span>
							</td>
						</tr>
						<tr valign="top">
							<td><strong><label for="pg_sas_image_float"><?php _e("Select alignment", 'pg-sas'); ?></label></strong></td>
							<td>
								<select id="pg_sas_image_float" name="pg_sas_image_float" style="width: 200px">
									<option value="alignleft"><?php _e("Align to the left", 'pg-sas'); ?></option>
									<option value="alignright"><?php _e("Align to the right", 'pg-sas'); ?></option>
									<option value="aligncenter"><?php _e("Align to the centre", 'pg-sas'); ?></option>
								</select><br />
								<span class="description"><?php _e('Select the alignment of this image.', 'pg-sas') ?></span>
							</td>
						</tr>
						<tr valign="top">
							<td><strong><label for="pg_sas_width"><?php _e("Width/Height: ", 'pg-sas'); ?></label></strong></td>
							<td>
								<input name="pg_sas_width" id="pg_sas_width" type="text" value="" style="width: 50px" /> / 
								<input name="pg_sas_height" id="pg_sas_height" type="text" value="" style="width: 50px" /><br />
								<span class="description"><?php _e('Max width and/or height eg 150px', 'pg-sas') ?></span>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="mceActionPanel">
				<div style="float: left">
					<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'wpsc'); ?>" onclick="tinyMCEPopup.close();" />
				</div>

				<div style="float: right">
					<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'wpsc'); ?>" onclick="insertPGSASLink();" />
				</div>
			</div>
		</form>
	</body>
</html>