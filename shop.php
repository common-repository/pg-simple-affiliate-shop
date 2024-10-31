<?php
/*
Plugin Name: PG Simple Affiliate Shop
Plugin URI: http://www.peoplesgeek.com/plugins/pg-simple-affiliate-shop/
Tags: affiliate store, affiliate shop, affiliate product management, simple affiliate page, affiliate marketing
Description: This plugin allows you to manage and display a simple list of affiliate products and banners
Version: 1.5
Author: PeoplesGeek
Author URI: http://www.peoplesgeek.com
Text Domain: pg-sas
Domain Path: /languages

	Copyright 2012 Brian Reddick

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
    
    http://www.gnu.org/licenses/license-list.html#GPLCompatibleLicenses

*/
load_plugin_textdomain( 'pg-sas', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

$shop = new PGSimpleAffiliateShop(); 
register_activation_hook( __FILE__ , 'pg_sas_activate');
register_deactivation_hook( __FILE__ , 'pg_sas_deactivate');

/*
TODO: Add fields to quick edit menu http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu and http://www.ilovecolors.com.ar/saving-custom-fields-quick-bulk-edit-wordpress/
TODO: Get icon on edit screen http://wptheming.com/2010/11/how-to-use-custom-post-type-icons/ and http://randyjensenonline.com/thoughts/wordpress-custom-post-type-fugue-icons/
TODO: and http://shibashake.com/wordpress-theme/modify-custom-post-type-icons
TODO: Add ability to 'move' an image from a remote URL to the local server (helps with import/export)
TODO: Remove the 'insert gallery' button from the gallery tab used by the uploader (or work out what galleries would look like)
		http://wordpress.stackexchange.com/questions/36531/adding-a-button-to-the-media-uploader
TODO: Check if video can show in the shop
TODO: Allow an image to show at more than one size (ie upload a big version, show the small version but give a popup to the original size image) a variation of this is http://wordpress.org/support/topic/more-images-maybe-lightbox?replies=1
TODO: Put validation on the link/URL field so that if people copy extra stuff from Commission Junction it will warn them and not fail
TODO: Made image in shop clickable () - need to add this to the admin pannel as an option
TODO: Set up a file import for products from a set format
TODO: Implement a exerpt field or read-more to optionally limit the text on the main page if there is a lot of content
TODO: Consider adding a banner rotation option (http://wordpress.org/support/topic/banner-rotation-1?replies=1)
TODO: Look at making some other parts of the css customisable from the settings page.
TODO: Investigate how to have the single page fit into the existing theme - eg so the category at the bottom of the page shows the SAS category not the page category
TODO: Add other sort options such as by date added or alpha by title as well as the sort order by drag and drop to the shortcode and shortcode builder
*/

class PGSimpleAffiliateShop{
	
	/**
	 * Set up the variables for this class
	 */
	private $meta_prefix='pgeek_sas_';
	private $shopURI ;
	private $shopDIR ;
	private $data;
	private $upload_dir = 'sas-images';
	private $settings;
	private $slug = 'pg-sas';
	private $opt_shop_button_text;
	private $opt_shop_quote_show;
	private $opt_shop_image_clickable;
	private $opt_banner_button_text;
	private $opt_banner_button_show;
	private $opt_banner_hover_image;
	private $opt_button_color;
	private $opt_inline_show_title;
	private $opt_shop_products_per_page;
	private $opt_page_control_location;
	private $opt_shop_use_excerpt;
	
							
	/**
	 * Constructor for the class and where all actions and filters are defined
	 */
	function PGSimpleAffiliateShop(){
		
		$this->data = array(	'description' =>array( 'textarea', 'p',   __('This description of the product is displayed beside the product image on shop pages only', 'pg-sas' ) ),
								'image' => 		array( 'image',	   'p b', __('The image for the product or banner display', 'pg-sas' ) ),
								'cost' => 		array( 'text',     'p',   __('Displayed by the image in shop pages only', 'pg-sas' ) ),
								'link' =>		array( 'link',     'p b', __('Your affiliate link or URL, used as the target for banner images and buy now buttons', 'pg-sas' ) ),
								'testimonial' =>array( 'textarea', 'p',   __('An optional testimonial for the product, shown at the bottom of the product in shop pages only', 'pg-sas' ) ),
								'customer' => 	array( 'text',     'p',   __('Optional name for the person giving the testimonial , shown under the testimonial in shop pages only', 'pg-sas' ) ),
								//'logo' => 		array( 'image', 'p' ,'The customer for the product display'),
								);
		$this->shopURI = plugin_dir_url(__FILE__);
		$this->shopDIR = plugin_dir_path(__FILE__);
		$options = $this->get_or_create_settings();
		
		add_action('init', array( $this , 'add_custom_type' ) );
		add_action('init', array( $this , 'add_custom_taxonomy' ) );
		add_shortcode('pg_sas_shop', array( $this , 'shortcode_shop' ) );
		add_shortcode('pg_sas_banner', array( $this , 'shortcode_banner' ) );
		add_shortcode('pg_sas_image', array( $this , 'shortcode_image' ) );
		add_shortcode('pg_dummy_url', array( $this , 'shortcode_dummy_url' ) ); // used for testing the link shortcode
		add_action('wp_enqueue_scripts', array( $this , 'enque_shop_css' ) );
		/*Add the filter to allow shortcodes to work in widget text areas */
		add_filter('widget_text', 'do_shortcode');
		
		if ( is_admin() ) {	// only load these if we are on an admin screen since they are only called there
			require_once ( dirname( __FILE__ ) . '/inc/pg-options.php' );
			$setting_pages = array('pages' => array('general'=> array( 'title'=> __('General Options', 'pg-sas' ) , 			'fields' => array( $this , 'show_general_settings')),
													'help' => array( 'title'=> __('Help', 'pg-sas' ) , 	'fields' => array( $this , 'show_help_settings')),
													),
									'default' => 'general'	);
			
			$this->settings = new PG_OptionsSupport(__('PG Simple Affiliate Shop', 'pg-sas' ) , true, $this->slug, 'pgeek_sas', $setting_pages);
			
			add_action( 'save_post', array( $this , 'save_meta' ) ,1 , 2 ); 
			add_action( 'save_post', array( $this ,'save_default_post_taxonomy'), 1, 2 );
			add_filter( 'wp_insert_post_data',array( $this ,'set_post_data'), 10, 2);
			add_filter( 'manage_edit-pgeek_sas_sortable_columns', array( $this , 'sortable_columns' ) );
			add_action( 'manage_pgeek_sas_posts_custom_column', array( $this , 'populate_columns' ), 10, 2 );
			add_filter( 'manage_pgeek_sas_posts_columns', array( $this , 'add_columns' ) );
			add_action( 'admin_enqueue_scripts', array( $this , 'remove_autosave') );
			add_action( 'admin_menu', array( $this ,'register_reorder_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this , 'enqueue_reorder_scripts' ) );
			add_action( 'wp_ajax_pgeek_sas_update_post_order', array( $this , 'ajax_update_post_order' ) );
			
			add_action('admin_print_scripts', array( $this , 'media_upload_scripts'));
			add_action('admin_print_styles', array( $this , 'media_upload_styles'));
			add_filter( 'get_media_item_args',  array( $this ,'force_add_to_post' ) );
			add_filter('upload_dir', array( $this , 'configure_upload_dir') );
			add_filter('media_upload_tabs', array( $this , 'media_upload_tabs') ,20 );
			//TODO: Add a tab that shows only items from the $upload_dir directory to make them easier to select (ie not all media in library)
			//add_filter('parse_query', array( $this , 'customize_media_library_query') );
			
			//Add buttons and TinyMCE plugin to the toolbar
			add_action( 'admin_init', array( $this, 'action_admin_init' ) );
			add_action( 'wp_ajax_pg_sas_shortcode_generator', array( $this, 'ajax_action_shortcode_generator' ) );
		}
	}
	
	/**
	 * Set up the defaults and one off values when the plugin is activated
	 */
	function activate(){
		$this->add_custom_type();
		$this->add_custom_taxonomy();
		$this->create_taxonomy_entries();
		// flush the rules of the user will need to manually save permalink settings
		flush_rewrite_rules();
	}
	
	/**
	 * Leave the defaults but flush the rewrite rules so that the custom post and taxonomy are no longer registered
	 */
	function deactivate(){
		//flush the rewrite rules since the taxonomy and custom post are no longer registered
		flush_rewrite_rules();
		
	}
	
	/* ============  TinyMCE button and plugin ============ */
	
	function action_admin_init(){
	    // only hook up these filters if we're in the admin panel, and the current user has permission
	    // to edit posts and pages
	    if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
	        add_filter( 'mce_buttons', array( $this, 'filter_mce_button' ) );
	        add_filter( 'mce_external_plugins', array( $this, 'filter_mce_plugin' ) );
	    }
	}
	
	function filter_mce_button( $buttons ) {
	    // add a separation before our button, here our button's id is &quot;mygallery_button&quot;
	    array_push( $buttons, '|', 'pg_sas_button' );
	    return $buttons;
	}
	 
	function filter_mce_plugin( $plugins ) {
	    // this plugin file will work the magic of our button
	    $plugins['pg_sas_button'] = plugin_dir_url( __FILE__ ) . 'js/pg_sas_plugin.js';
	    return $plugins;
	}
	
	/**
 * Display the shortcode generator.
 *
 * @since  3.8.9
 * @access private
 */
	function ajax_action_shortcode_generator() {
		require_once( $this->shopDIR . 'js/pg-sas-popup.php' );
		exit;
	}
		
		
	/* ============  Image Uploading and Gallery ============ */	
	
/**
	 * Force the 'add to post' button selectively for only this post type
	 */
	function get_media_item( $attachment_id, $args = null ){
		// if the attachment is one of ours then set the 'send' argument to true so the 'attach to post' button shows up
		// TODO: complete this so we don't have to force all attachments to send in the function below
	}

/**
	 * As the post type does not support editor the upload button does not show 'add to post' by default so force it on
	 */
	function force_add_to_post($args){
		// Our custom post type does not have the editor so the 'add to post' button is missing unless we force it on
		$args['send'] = true;
		return $args;
	}	

	/**
	 * Add scripts to support the media uploader
	 */
	function media_upload_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script('sas-upload', $this->shopURI.'js/sas-media-upload.js', array('jquery','media-upload','thickbox'));
		wp_enqueue_script('sas-upload');
	}

	/**
	 * Add styles to support the media uploader
	 */
	function media_upload_styles() {
		wp_enqueue_style('thickbox');
	}

	/**
	 * Add change tabs on the media uploader if this is our custom post
	 */
	function media_upload_tabs($tabs){ 
		
		if (isset($_GET['post_id'])){
			if (  get_post_type($_GET['post_id']) == 'pgeek_sas' ){
				//TODO: Add custom page with only shop images $tabs['shop]'] = __('Shop Gallery');
				unset($tabs['type_url']); // we don't want to encourage images on other sites
			}
		}
		return($tabs); 
	}

	/**
	 * Change the upload directory for images that we upload to keep them all together and seperate from other media 
	 */
	function configure_upload_dir($path_data){
		$is_pg_sas = false;
		$is_pg_sas_attachment = false;
		if (isset($_POST['post_id']) && get_post_type($_POST['post_id']) == 'pgeek_sas') 
			$is_pg_sas = true;
			
		if (isset($_POST['attachment_id'])&&  get_post_type(get_post($_POST['attachment_id'])->post_parent) == 'pgeek_sas') 
			$is_pg_sas_attachment = true;
		
			if ( $is_pg_sas || $is_pg_sas_attachment ){
				//only change the subdirectory then all other media settings continue to work
				$path_data['path'] = $path_data['basedir'] . '/'. $this->upload_dir; 
			    $path_data['url'] = $path_data['baseurl'] . '/'. $this->upload_dir; 
			    $path_data['subdir'] = "/" . $this->upload_dir;;
			    $path_data['error'] = false;
			}
	    return $path_data;
	}
	
	/**
	 * When showing the media library only show those from the custom subdirectory
	 */
	function customize_media_library_query($query){
		//Not currently used! Must be applied only when in media upload or all queries using meta_query fail
	    if (isset($_GET['action']) && ( ! $_GET['action'] == 'edit'))
	    $query->query_vars['meta_query'] = array(
	      									 array(
									           'value' => $this->upload_dir, //$upload_dir,
									           'compare' => 'LIKE' )
	      									 );
	}

	/* ============  Custom Post, Taxonomy and Meta boxes============ */		

	/**
	 * Set up the custom type that holds all the shop products and banners
	 */
	function add_custom_type(){
		$args = array(
			'show_ui' => true,
			'hierarchical' => true,
			'public' => true,
			'supports' => array('title','thumbnail'),//'excerpt'), // ,'author'
			'menu_icon' => $this->shopURI. "images/cash-register-icon.png", 
			'labels' => array(
				'name' => __('Shop', 'pg-sas' ) ,
				'all_items' => __('All Products', 'pg-sas' ) ,
				'singular_name' => __('Product', 'pg-sas' ) ,
				'add_new' => __('Add New Product', 'pg-sas' ) ,
				'add_new_item' => __('Add New Product', 'pg-sas' ) ,
				'edit_item' => __('Edit', 'pg-sas' ) ,
				'new_item' => __('New', 'pg-sas' ) ,
				'view_item' => __('View', 'pg-sas' ) , //:TODO make this work? Perhaps custom page
				'search_items' => __('Search Products', 'pg-sas' ) ,
				'not_found' => __('No products found', 'pg-sas' ) ,
				'not_found_in_trash' => __('No products found in trash', 'pg-sas' ) ,
				),
			'description' => __('Products to be displayed as affiliate links in either banner advertisements or with description and details', 'pg-sas' ) ,
			'rewrite' => array('slug' => 'sasproduct'),
			'register_meta_box_cb' => array( $this , 'add_post_meta_box'),
			'taxonomies' => array('pg_sas_type')
			);
			
			register_post_type('pgeek_sas', $args);

	}	
	
	/**
	 * Set up the custom taxonomy to handle the different types of products<br/>
	 * Banners, Shop Products, then sub products like On Sale
	 */
	function add_custom_taxonomy(){
		$args = array(
			'hierarchical' => true,
			'show_tagcloud' => false,
			'labels' => array(
				'name' => __('Product Type', 'pg-sas' ) ,
				'singular_name' => __('Product Type', 'pg-sas' ) ,
				'search_items' => __('Search product types', 'pg-sas' ) ,
				'popular_items' => __('Popular product types', 'pg-sas' ) ,
				'all_items' => __('All Types', 'pg-sas' ) ,
				'parent_item' => __('Parent Type', 'pg-sas' ) ,
				'edit_item' => __('Edit Product Type', 'pg-sas' ) ,
				'update_item' => __('Update Product Type', 'pg-sas' ) ,
				'add_new_item' => __('Add New Product Type', 'pg-sas' ) ,
				'new_item_name' => __('New Product Type'), 'pg-sas' ) ,
			'rewrite' => array('slug' => 'sastype')
		);
		register_taxonomy('pg_sas_type', array('pgeek_sas'), $args);
	}
	
	/**
	 * Used by Activate to create the default Shop taxonomy entries for the different products
	 */
	function create_taxonomy_entries(){
		wp_insert_term( __('Banner Advert', 'pg-sas' ) , 'pg_sas_type' , array(	'slug' => 'banner-advert', 
																	'description'=> __('These products will be shown in the list of banner adds' , 'pg-sas' ) ));
		$p = wp_insert_term( __('Shop Product', 'pg-sas' ) ,'pg_sas_type', array(	'slug' => 'shop-product',
																					'description'=> __('These products will be shown in the shop', 'pg-sas' )  ));
		if (is_wp_error($p)) // if the terms already exist then $p will be an error and we can assume the others exist and exit
			return;
			
		wp_insert_term( __('On Sale', 'pg-sas' ) , 'pg_sas_type', array(	'slug' => 'on-sale',
																'parent'=> $p['term_id'], 
																'description'=> __('These products may be highlighted as on sale', 'pg-sas' ) ));
		delete_option("pg_sas_type_children"); // A fix to correct the bug where children do not show in the UI when added programmatically  	
	}
	
	/**
	 * Set up the meta box that is used to collect all the product and banner information
	 */
	function add_post_meta_box(){
		add_meta_box('pg_sas_meta', __('Affiliate Product Details', 'pg-sas' ) , array( $this , 'show_sas_meta_box'),'pgeek_sas','normal','high' );
	}
	
	/**
	 * Display the meta box on the custom post screen
	 */
	function show_sas_meta_box($post){
	    wp_nonce_field( plugin_basename( __FILE__ ), 'pg_sas_noncename' );
	    echo '<table>';
	    foreach ($this->data as $key => $value) {
			$index = $this->meta_prefix . $key;
			$required = ( stripos($value[1], 'b') >0 ? '*':'' );
			echo '<tr><td>'.ucfirst($key). $required . '</td><td>' ;
	    	switch ($value[0]) {
	    		case 'checkbox':
	    			$selected = (get_post_meta($post->ID, '_' . $index, true) == 'true')? 'checked="yes"' : '' ;
		    		echo '<input type="checkbox" name="'.$index.'" '.$selected.' value="true"  />';
		    	break;
		    	
	    		case 'textarea';
	    			echo '<textarea name="'.$index.'" type="text" rows="4" cols="50" title="'.$value[2].'" >' .get_post_meta($post->ID, '_' . $index, true) .'</textarea>';
	    			//echo '<br/>' . $value[2];
	    		break;
	    		case 'image';
	    			// embed the post_id in 'rel' for the image upload java script
	    			//http://wordpress.stackexchange.com/questions/18678/plugging-into-the-media-library-to-upload-images-not-associated-with-any-post
	    			echo '<input type="text" id="'.$index.'" name="'.$index.'" size="55" value="' . get_post_meta($post->ID, '_' . $index, true)  . '" title="'.$value[2].'" />';
	    			echo '<input type="button" class="button" name="'.$index.'_button" id="'.$index.'_button" value="Browse" rel="'. $post->ID .'" />';
	    			//echo '<br/>' . $value[2];
	    		break;
	    		default:
	    			echo '<input type="text" name="'.$index.'" size="55" value="' . get_post_meta($post->ID, '_' . $index, true)  . '" title="'.$value[2].'" />';
	    			//echo '<br/>' . $value[2];
	    		break;
	    	}
	    	echo '</td><tr>' ;
		}	
	    echo '</table>';

	    echo '<p>' . __('* These fields are used by the banner advertisement style of display and are the minimum required', 'pg-sas' ) .'</p>';
	    
	    if (get_option('image_default_link_type') != 'file'){
	    	echo '<p class="pg-sas-file_not_default"><strong>'.__('Caution:', 'pg-sas' ) .'</strong> '.__('Your system default for linking images is not set to "file"', 'pg-sas' ).'<br/>';
	    	echo __('You must make sure the file is selected when using the browse button / media uploader above or the image field will appear blank. ', 'pg-sas' ) ;
	    	echo __('Ensure you choose the "File URL" button in the uploader before clicking on "Insert into Post" ', 'pg-sas' ) .'</p>';
	    }
	}  
	
	/**
	 * Save the metadata when the post is saved
	 */
	function save_meta($post_id, $post) {
		// a number of tests to see if we should save the data - bail if failed
		if ( ! isset($_POST['pg_sas_noncename']) )
			return;
		if( ! wp_verify_nonce( $_POST['pg_sas_noncename'], plugin_basename( __FILE__ ) ) ) 
	    	return;
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	    	return;
		if(  ( 'post' == $_POST['post_type'] )  && ( ! current_user_can( 'edit_post', $post_id ) )  )
			return;
		if( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) 
			return;
	  
		foreach ($this->data as $key => $value) {
			$index = $this->meta_prefix . $key;
			switch ($key) {
				case 'banner':
				$checked = (isset($_POST[$index]) )? 'true':'false';
					update_post_meta($post_id, ( '_' . $index ), $checked);
				break;
				
				default:
				if (isset($_POST[$index]) ) {
					if ( current_user_can('unfiltered_html') ){
						update_post_meta($post_id, ( '_' . $index ), $_POST[$index]);
					} else{
						update_post_meta($post_id, ( '_' . $index ), stripslashes( wp_filter_post_kses( addslashes($_POST[$index]) ))); // wp_filter_post_kses() expects slashed
					}
				}
				break;
			}
		}	
	}
	
	/**
	 * Set the post_content for the product as the shortcode so it displays if viewed separately<br/>
	 * The 'showtitle' is set to false to prevent double up of title with heading
	 * The product_id is set to the post id so only this product shows on the page
	 */
	function set_post_data($data, $postarr) {
		global $post;
		
		if ( isset($post) ) { // $post is not set if called from the quickedit menu
		    // set the post content to be the product or banner as appropriate
		    if ( $data['post_type']== 'pgeek_sas' ){
		    	$data['post_content'] = '[pg_sas_shop showtitle="false" product_id='. $post->ID . ']';
		    }
		}
	    return $data;
	}
	
	/**
	 * There is odd behaviour with autosave showing warnings so it is turned off for this post type
	 * It will continue to function as normal for all other pages
	 */
	function remove_autosave() {
	    if ( 'pgeek_sas' == get_post_type() )
	        wp_dequeue_script( 'autosave' );
	}
		
	/**
	 * Force a default taxonomy entry when saving if none was chosen on the post screen
	 */
	function save_default_post_taxonomy( $post_id, $post ) {
	    if ( 'publish' == $post->post_status ) {
	        $defaults = array( 'pg_sas_type' => array( 'banner-advert' ) );
	        $taxonomies = get_object_taxonomies( $post->post_type );
	        foreach ( (array) $taxonomies as $taxonomy ) {
	            $terms = wp_get_post_terms( $post_id, $taxonomy );
	            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
	                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
	            }
	        }
	    }
	}

	/* ============  Display Shops and Banners ============ */
	
	/**
	 * Load the css for the shop using the standard enqueue
	 */	
	function enque_shop_css(){
		if ( file_exists (get_stylesheet_directory() . '/pg-sas.css')){
			wp_register_style('pg-sas', get_stylesheet_directory_uri() . '/pg-sas.css' );
		} else {
			wp_register_style('pg-sas', $this->shopURI.'css/pg-sas.css');
		}
		wp_enqueue_style( 'pg-sas' );
	}
	
	/**
	 * Output the shop products as a result of using the shortcode [pg_sas_shop]<br>
	 * Shortcode parameters are:<br/>
	 * * showtitle (boolean) default true<br/>
	 * * product_id (numeric)<br/>
	 * * category (slug of Type) default 'shop-product'<br/>
	 * * showchildren (boolean) show children of category default true<br/>
	 * * products_per_page (numeric) how many products to show on a page
	 */
	function shortcode_shop( $atts, $content = null, $tag ){
		extract( shortcode_atts( array ('showtitle' => 'true', 'product_id' => 0, 'category' => 'shop-product', 'showchildren'=> true, 'products_per_page' => $this->opt_shop_products_per_page), $atts ) );
		$showchildren = ($showchildren === true || $showchildren == 'true')? true:false; // can't pass anything other than string and variable must be boolean
 		
		$shop_page = get_query_var('page') ? get_query_var('page') : 1;
		
		$query_args = array('posts_per_page'=> $products_per_page , 'post_type' => 'pgeek_sas','orderby' =>'menu_order', 'order' => 'ASC', 'paged' => $shop_page);

		if ( $product_id >0 ){
			$query_args['page_id'] = $product_id;
		} else {
			$query_args['tax_query'] = array( array( 'taxonomy' => 'pg_sas_type', 'field' => 'slug', 'terms' => $category , 'include_children' => $showchildren ) );
		}
		
		$shop_query = new WP_Query( $query_args ); 
		$max_num_pages = $shop_query->max_num_pages ;
		
		if ($shop_query->have_posts()) {
				$output = '<div id="shop-product-container">'."\n";
				// if there is pagination then show the pages available
				if ( in_array( $this->opt_page_control_location , array('top','both') ) )
					$output .= $this->show_pagenation( $max_num_pages);
						
				$show_quote = ($this->opt_shop_quote_show)?'pg-sas-testimonial show-quote' : 'pg-sas-testimonial hide-quote';
				while($shop_query->have_posts()) : $shop_query->the_post();
					$post_id = get_the_ID();
					$output .= '<div class="pg-sas-product pg-sas-product-'. $post_id . '">'."\n";
					$output .= ($showtitle == 'true')? '	<h2 class="pg-sas-title"><a href="'. get_permalink().'">' . get_the_title() .'</a></h2>'."\n" :'';
					if ($this->opt_shop_image_clickable)	
						$output .= '<a " href="'.do_shortcode( $this->get_metavalue($post_id, 'link') ).'" target="_blank" rel="nofollow">'."\n";;
					$output .= '<img class="alignleft pg-sas-image" src="'. $this->get_metavalue($post_id, 'image'). '" alt="'. get_the_title(). '" >'."\n";
					if ($this->opt_shop_image_clickable)
						$output .= '</a>'."\n";
					$output .= nl2br( do_shortcode( $this->get_metavalue($post_id, 'description') ) )."\n";
					$output .= '<p class="pg-sas-cost">'. do_shortcode( $this->get_metavalue($post_id, 'cost') ).'</p>'."\n";
					$output .= '<a class="pg-sas-buy '. $this->opt_button_color .'" href="'.do_shortcode( $this->get_metavalue($post_id, 'link') ).'" target="_blank" rel="nofollow">'.$this->opt_shop_button_text.'</a>'."\n";
					$output .= '<div style="clear:both;"></div>'."\n";
					if ($this->get_metavalue($post_id, 'testimonial') !='' ){
						$output .= '<div class="'. $show_quote . '" >'."\n";
						$output .= nl2br( do_shortcode( $this->get_metavalue($post_id, 'testimonial') ) )."\n";
						$output .= '</div>'."\n";
						$output .= '<div class="pg-sas-customer">'. do_shortcode( $this->get_metavalue($post_id, 'customer') ).'</div>'."\n";
					}
					$output .= '</div>'."\n";
	
				endwhile; 
				
				if ( in_array( $this->opt_page_control_location , array('bottom','both') ) )
					$output .= $this->show_pagenation( $max_num_pages);

				$output .= '</div>';
				
				wp_reset_postdata();
				
		} else {
			$output = __('there are no products to show', 'pg-sas' ) ."\n";
		}
		return "\n<!-- Begin PG Simple Affiliate Shop -->\n" . $output . "\n<!-- End PG Simple Affiliate Shop -->\n";
	}
	
	/**
	 * Helper function to manage the display of pagenation for the shop
	 * Takes into account if there are other query variables already (but does not allow for another once called page)
	 */
	private function show_pagenation($max_num_pages = 1){
		// bail and do nothing if we don't need pagenation
		if ($max_num_pages <=1 ){
			return '';
		}
		
		$paged = (get_query_var('page')) ? get_query_var('page') : 1;
		$output = '<div id="pg-sas-pagenation">';

		$class = ($paged == 1 )? ' class="current" ': '' ;
		if ( $paged == 1 ){
			$output .= '<span class="disabled">&laquo; ' .__('Previous', 'pg-sas' ) . '</span>';
		}else{
			$output .= '<a href="' . add_query_arg('page',$paged -1 ) . '">&laquo;' .__('Previous', 'pg-sas' ) . '</a>';
		}	
		
		$output .= '<a href="' . add_query_arg('page',1) . '"' . $class .'>1</a>';	
				
		for($i = 2; $i <= $max_num_pages ; $i++) { 
			$class = ($paged == $i )? ' class="current" ': '' ;
			$output .= '<a href="' . add_query_arg('page',$i) . '"' . $class .'>' . $i . '</a>';
		}

		if ( $paged == $max_num_pages ){
			$output .= '<span class="disabled">' .__('Next', 'pg-sas' ) . ' &raquo;</span>';
		}else{
			$output .= '<a href="' . add_query_arg('page',$paged + 1 ) . '">' .__('Next', 'pg-sas' ) . ' &raquo;</a>';
		}
		
		$output .= '</div>';
		
		return apply_filters('pg_sas_pagenation',  $output, $max_num_pages, $paged );
	}
	
	
	/**
	 * Output the shop banners as a result of using the shortcode [pg_sas_banner]
	 */
	function shortcode_banner( $atts, $content = null, $tag ){
		extract( shortcode_atts( array ('showtitle' => 'true', 'product_id' => 0, 'category' => 'banner-advert', 'showchildren'=> true), $atts ) );
		$showchildren = ($showchildren === true || $showchildren == 'true')? true:false; // can't pass anything other than string and variable must be boolean
 		
		$query_args = array('posts_per_page'=>-1, 'post_type' => 'pgeek_sas','orderby' =>'menu_order', 'order' => 'ASC');

		if ( $product_id >0 ){
			$query_args['page_id'] = $product_id;
		} else {
			$query_args['tax_query'] = array( array( 'taxonomy' => 'pg_sas_type', 'field' => 'slug', 'terms' => $category , 'include_children' => $showchildren ) );
		}
		
		$shop_query = new WP_Query( $query_args ); 
		if ($shop_query->have_posts()) {
				$output = '<div id="shop-banner-container">';
				$banner_background = ( $this->opt_banner_hover_image )? 'pg-sas-banner pg-sas-show-background pg-sas-banner-' : 'pg-sas-banner pg-sas-banner-';

				while($shop_query->have_posts()) : $shop_query->the_post();
				$post_id = get_the_ID();
					$output .= '<div class="' . $banner_background . $post_id . '">';
					if ( $this->opt_banner_button_show )
						$output .= '<a class="pg-sas-banner-button pg-sas-buy '. $this->opt_button_color .' small" href="'. do_shortcode( $this->get_metavalue($post_id, 'link') ). '" title="'. get_the_title(). '" target="_blank" rel="nofollow">'.$this->opt_banner_button_text.'</a>';	
					$output .= '<a class="pg-sas-banner pg-sas-banner-img-link" href="'. do_shortcode( $this->get_metavalue($post_id, 'link') ). '" title="'. get_the_title(). '" target="_blank" rel="nofollow">';	
					$output .= '<img class="pg-sas-banner-img" src="'. $this->get_metavalue($post_id, 'image'). '"alt="'. get_the_title(). '" title="'. get_the_title(). '" ></a>';	
					$output .= '</div>';
				endwhile; 

				$output .= '</div>';
				
				wp_reset_postdata();
		} else {
			$output = "no banners";
		}
		return "\n<!-- Begin PG Simple Affiliate Shop Banner -->\n" . $output . "\n<!-- End PG Simple Affiliate Shop Banner -->\n";
	}
	
	/**
	 * Output the a test URL as a result of using the shortcode [pg_dummy_url]
	 */
	function shortcode_dummy_url( $atts, $content = null, $tag ){
		return "https://www.peoplesgeek.com/plugins/pg-simple-affiliate-shop/";
	}
	
	/**
	 * Output a single shop image with link using the shortcode [pg_sas_image]<br>
	 * Shortcode parameters are:<br/>
	 * * product_id (numeric)<br/>
	 * * float (string) alignleft, alignright or aligncenter<br/>
	 * * height(string) height eg '200px'<br/>
	 * * width (string) width eg '200px'<br/>
	 */
	function shortcode_image( $atts, $content = null, $tag ){
		extract( shortcode_atts( array ( 'product_id' => 0, 'float' => 'alignleft', 'width'=> '', 'height'=>'' ), $atts ) );
		
		$query_args = array('posts_per_page'=>-1, 'post_type' => 'pgeek_sas','orderby' =>'menu_order', 'order' => 'ASC', 'page_id' => $product_id);
			
		$shop_query = new WP_Query( $query_args ); 
		if ($shop_query->have_posts() ) {
				$output = '';
				$size = ('' == $width)? '':' width="'.$width.'" ';
				$size .= ('' == $height)? '':' height="'.$height.'" ';
				while($shop_query->have_posts()) : $shop_query->the_post();
					$post_id = get_the_ID();
					$title = ( $this->opt_inline_show_title )? ' title="'. get_the_title(). '"' : '';
					$output .= '<a href="'. do_shortcode( $this->get_metavalue($post_id, 'link') ). '" target="_blank" rel="nofollow" '.$title.' >';
					$output .= '<img class="'.$float.'" src="'. $this->get_metavalue($post_id, 'image'). '" alt="'. get_the_title(). '"'.$size.' ></a>'."\n";						
				endwhile; 
				
				wp_reset_postdata();
				
		} else { 
			$output = "<!-- product_id does not exist -->\n";
		}
		return "\n<!-- Begin PG Simple Affiliate Image -->\n" . $output . "\n<!-- End PG Simple Affiliate Image #$product_id -->\n";
	}
	
	
	
	/**
	 * Helper to get the meta value in a 'loop' (called internally only)
	 */
	private function get_metavalue($post_id, $name){
		return get_post_meta($post_id, '_'.$this->meta_prefix.$name, true);
	}
	
	/* ============  Sorting and Custom Column Support ============ */
	
	/**
	 * Specify the custom columns that appear for the custom post in the back end
	 */
	function add_columns( $cols ) {
		//'description' 'image' 'cost' 'link' 'testimonial' 'customer' 'logo'

		$cols = array(
		    'cb'       => '<input type="checkbox" />',
		    'type'      => __( 'Type/#',      'trans' ),
		    'title'      => __( 'Title',      'trans' ));
		foreach ($this->data as $key => $value) {
			$cols[$key] = ucfirst($key);
		}

	  	return $cols;
	}

	/**
	 * Specify the content of our new custom columns that appear for the custom post in the back end
	 */
	function populate_columns( $column, $post_id ) {
		//TODO: consider transients to improve the performance of this when we are doing sorting etc - particularly the image existence checks
		global $post;
		switch ( $column ) {
			case "type":
				$terms = wp_get_post_terms($post_id,'pg_sas_type',array('orderby' => 'name', 'order' => 'ASC'));  
				foreach ($terms as $term) {  
				    echo $term->name. '<br/>';  
				} 
				echo "(#{$post_id})";
			break;
			default:
				if (isset( $this->data[$column] )){
					$length = strlen($this->get_metavalue($post_id,$column));
					$content = 	$this->get_metavalue($post_id,$column);
					switch ($this->data[$column][0]) {
						case 'textarea':
						case 'text':
							echo wp_trim_words($content , 10);
							//echo ( strlen($content) > 40 )? substr($content, 0 , 40) . '...' : $content;
						break;
						case 'image':
							if ($length ==0) {
								echo '<img class="pg-sas-status" src="' . $this->shopURI. '/images/bad.jpg"> '. __('Not Entered', 'pg-sas' ) ;
								break;
							}
							echo '<img class="pg-sas-status" width="60px" src="' . $content . '"><br/>';
							$size = ( false !== get_transient('pg-sas-image-size-' . $post_id ) ) ? get_transient('pg-sas-image-size-' . $post_id ) : @getimagesize($content) ;
							//echo (false !== get_transient('pg-sas-image-size-' . $post_id )?'not false':'false');
							if ($size ){
								$localfile = parse_url($content);
								if ( stripos(get_home_url(),$localfile['host']) == 0 ){
									$msg = __('This image does not appear to be hosted on your local site, unless you are using a CDN you may not have control over this image','pg-sas');
									echo '<img class="pg-sas-status" src="' . $this->shopURI. '/images/warn.jpg" alt="'. $msg .'" title="'. $msg .'"> '. __('Not Local', 'pg-sas' ) ;
									set_transient('pg-sas-image-size-' . $post_id, $size, 60);
								}
							} else {
								echo ' ';
							}
						break;
						case 'link':
							if ($length ==0) {
								echo '<img class="pg-sas-status" src="' . $this->shopURI. '/images/bad.jpg"> '. __('Not Entered', 'pg-sas' ) ;
								break;
							}
							echo ( strlen($content) > 40 )? substr($content, 0 , 40) . '...' : $content;
						break;
						default:
							$file = $length > 0 ? 'good':  'bad' ;
							echo '<img class="pg-sas-status" src="' . $this->shopURI . '/images/' . $file . '.jpg"><br/>';
							echo 'length: ' . strlen($this->get_metavalue($post_id,$column));
						break;
					}
				}
			break;
	      }
	}

	/**
	 * Specify which of the our new custom columns are sortable
	 */
	function sortable_columns() {
		$cols = array( 	'type'	=> 'type',
						'title'	=> 'title' );
		foreach ($this->data as $key => $value) {
			if ($value[0] == 'text')
				$cols[$key] = $key;
		}
		return $cols;
	}

	/**
	 * Display a form that will be attached to jQuery and ajax to allow the entries to be reordered easily
	 */
	function reorder_page() {
		$products = new WP_Query( array( 'post_type' => 'pgeek_sas', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'menu_order' ) ); 
		
		$output  = '<div class="wrap">'. screen_icon(); //TODO: add <div class="icon32"><img title="" src="'. plugin_dir_url(__FILE__). "/../images/move.png" .'" alt="Move Icon" width="30" height="30" />....
		$output .= '<h2>'. __('Reorder Products', 'pg-sas' ) .'</h2>';
		$output .= '<p>'. sprintf( __('Simply drag a product up or down by clicking and draging on the %1$s and the new order will be saved automatically', 'pg-sas' ),
										'<img title="" src="'.$this->shopURI. "images/move.png" .'" alt="' . __('Move Icon', 'pg-sas' ).'" width="30" height="30" />') .'</p>';
		$output .= '<table id="sortable-table" class="wp-list-table widefat fixed posts pg-sas-reorder">';
		$output .= '<thead>';
		$output .= '<tr>';
		$output_th  = '<th class="column-order pgeek-drag">'.__('Drag', 'pg-sas' ).'</th>';
		$output_th .= '<th class="column-type">'.__('Type', 'pg-sas' ).'</th>';
		$output_th .= '<th class="column-title">'.__('Title', 'pg-sas' ).'</th>';
		$output_th .= '<th class="column-description">'.__('Description', 'pg-sas' ).'</th>';
		$output_th .= '<th class="column-image">'.__('Image', 'pg-sas' ).'</th>';
		$output .= $output_th;
		$output .= '</tr>';
		$output .= '</thead>';
		$output .= '<tbody data-post-type="pgeek_sas">';
		
		if ( ! $products->have_posts() )
			$output .= '<td colspan="5" class="column-none">'.__('You have not added any products yet. Add some and then reorder them here.', 'pg-sas' ).'</td>';
		
		while ( $products->have_posts() ) : $products->the_post();
			$post_id = get_the_ID();
			$output .= '<tr id="post-' . $post_id.'">';
			$output .= '<td class="column-order"><img title="" src="'. $this->shopURI . "images/move.png" .'" alt="' . __('Move Icon', 'pg-sas' ).'" width="30" height="30" /></td>';
			$terms = wp_get_post_terms($post_id,'pg_sas_type',array('orderby' => 'name', 'order' => 'ASC')); 
			$output .= '<td class="column-type">'; 
				foreach ($terms as $term) {  
					$output .=  $term->name. '<br/>';  
				}
			$output .= '</td>';
			$output .= '<td class="column-title">' . get_the_title() . '</td>';
			$content = $this->get_metavalue($post_id, 'description');
			$output .= '<td class="column-description">' .( ( strlen($content) > 40 )? substr($content, 0 , 40) . '...' : $content )  .  '</td>';
			$output .= '<td class="column-image"><img width="60px" src="' . $this->get_metavalue($post_id, 'image') . '"></td>';
			$output .= '</tr>';
		endwhile;
		$output .= '</tbody>';
		$output .= '<tfoot>';
		$output .= '<tr>';
		$output .= $output_th;
		$output .= '</tr>';
		$output .= '</tfoot>';
		$output .= '</table>';
		
		// Reset Post Data
		wp_reset_postdata();
		
		$output .= '</div>';
	
		echo $output;
	}
	
	/**
	 * Register the menu to be used to reorder products
	 */
	function register_reorder_menu() {
		add_submenu_page(
			'edit.php?post_type=pgeek_sas',
			__('Reorder Products', 'pg-sas' ),
			__('Product Reorder', 'pg-sas' ),
			'edit_pages', 
			'pg-sas-reorder',
			array($this , 'reorder_page')
		);
	}

	/**
	 * Enqueue the script that is used to reorder products
	 */
	function enqueue_reorder_scripts(){
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'pgeek-sas-admin-scripts', $this->shopURI. 'js/pgeek-sas-sort-script.js' );
		wp_enqueue_style('pg-sas-admin',$this->shopURI . "css/pg-sas-admin.css");
	}
	
	/**
	 * Process the ajax request that reorders products
	 */
	function ajax_update_post_order(){
		global $wpdb;
	
		$post_type     = $_POST['postType'];
		$order        = $_POST['order'];
	
		foreach( $order as $menu_order => $post_id )
		{
			$post_id         = intval( str_ireplace( 'post-', '', $post_id ) );
			$menu_order     = intval($menu_order);
			wp_update_post( array( 'ID' => $post_id, 'menu_order' => $menu_order ) );
		}
	
		die( '1' );
	}
	
	/* ============   Options Pages ============ */
	
	/**
	 * Get the option set name for each option page (keeping formatting in one place in case of changes)
	 */
	function optionset_name($page='help'){
		return $this->slug.'-'.$page.'-opt';
	}
	
	/**
	 * Return the option settings during construction and creating the defaults if they are not found
	 * Setting defaults is located here with the option pages for cut and paste convenience 
	 */
	private function get_or_create_settings(){
		$options = get_option($this->optionset_name('general'));
		if (! $options ){
			$options = array(
							'shop_buy_button_text' => 'Buy Now',
							'shop_show_quotes' => 'on',
							'banner_buy_button_text' => 'Buy Now',
							//'banner_show_button' =>  missing from array = off
							'banner_hover_image' => true,
							'button_color' => 'pg-sas-orange',
							//'inline_show_title' =>  missing from array = off
							'shop_products_per_page' => -1,
							'shop_page_control_location' => 'both',
							//'shop_image_clickable' => missing from array = off,
							//'shop_use_excerpt' => missing from array = off,
					
			);
			update_option($this->optionset_name('general'), $options);
		}
		$this->opt_banner_button_show = isset($options['banner_show_button']);
		$this->opt_banner_button_text = $options['banner_buy_button_text'];
		$this->opt_shop_button_text = $options['shop_buy_button_text'];
		$this->opt_shop_quote_show = isset($options['shop_show_quotes']);
		$this->opt_shop_image_clickable = isset($options['shop_image_clickable']);
		$this->opt_shop_use_excerpt = isset($options['shop_use_excerpt']);
		$this->opt_banner_hover_image = isset($options['banner_hover_image']);
		$this->opt_button_color = isset($options['button_color'])? $options['button_color']:'pg-sas-orange';
		$this->opt_inline_show_title = isset($options['inline_show_title']); 
		$this->opt_shop_products_per_page = isset($options['shop_products_per_page'])? $options['shop_products_per_page']:-1;
		$this->opt_page_control_location = isset($options['shop_page_control_location'])? $options['shop_page_control_location']:'both';
				
		return $options;
	}
	
	/**
	 * Show the general settings page content and fields
	 */
	function show_general_settings(){	
		$optionset = $this->optionset_name('general');
		echo '<table>';
		echo '<tr><td><h3>'.__('Shop', 'pg-sas' ).'</h3></td></tr>';
		echo $this->settings->input_text($optionset,'shop_buy_button_text',array('before'=>'<tr><td>'.__('Buy Button Text', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		echo $this->settings->input_checkbox($optionset,'shop_show_quotes',array('before'=>'<tr><td>'.__('Show Quote Images for testimonials', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		echo $this->settings->input_checkbox($optionset,'shop_image_clickable',array('before'=>'<tr><td>'.__('Shop image is clickable as well as button', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		//echo $this->settings->input_checkbox($optionset,'shop_use_excerpt',array('before'=>'<tr><td>'.__('Use Excerpt text when listing products', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		echo $this->settings->input_text($optionset,'shop_products_per_page',array('before'=>'<tr><td>'.__('Products per page', 'pg-sas' ).'</td><td>', 'after'=> __(' -1 = unlimited', 'pg-sas' ).'</td></tr>'));
		echo $this->settings->input_radiobutton($optionset,'shop_page_control_location',array('before'=>'<tr><td>'.__('Show Pagenation controls', 'pg-sas' ).'</td><td>', 'after'=>__(' Top ', 'pg-sas' ), 'value'=>'top'));
		echo $this->settings->input_radiobutton($optionset,'shop_page_control_location',array('before'=>' ', 'after'=>__(' Bottom ', 'pg-sas' ), 'value'=>'bottom'));		
		echo $this->settings->input_radiobutton($optionset,'shop_page_control_location',array('before'=>' ', 'after'=>__(' Both ', 'pg-sas' ).'</td></tr>', 'value'=>'both'));
		
		
		echo '<tr><td><h3>'.__('Banner', 'pg-sas' ).'</h3></td></tr>';
		echo $this->settings->input_text($optionset,'banner_buy_button_text',array('before'=>'<tr><td>'.__('Buy Button Text', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		echo $this->settings->input_checkbox($optionset,'banner_show_button',array('before'=>'<tr><td>'.__('Show Buy Button in Banner', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		echo $this->settings->input_checkbox($optionset,'banner_hover_image',array('before'=>'<tr><td>'.__('Show Hover Image in Banner', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));

		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>'<tr><td>'.__('Button Color', 'pg-sas' ).'</td><td>', 'after'=> __(' Black ', 'pg-sas' ), 'value'=>'pg-sas-black'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Gray ', 'pg-sas' ), 'value'=>'pg-sas-gray'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' White ', 'pg-sas' ), 'value'=>'pg-sas-white'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Orange ', 'pg-sas' ), 'value'=>'pg-sas-orange'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Red', 'pg-sas' ).'<br/>', 'value'=>'pg-sas-red'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Blue ', 'pg-sas' ), 'value'=>'pg-sas-blue'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Rosy ', 'pg-sas' ), 'value'=>'pg-sas-rosy'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Green ', 'pg-sas' ), 'value'=>'pg-sas-green'));
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Pink ', 'pg-sas' ), 'value'=>'pg-sas-pink'));		
		echo $this->settings->input_radiobutton($optionset,'button_color',array('before'=>' ', 'after'=>__(' Custom ', 'pg-sas' ).'<br />'.__('* If you choose Custom you must add your own CSS to the stylesheet or no formatting used', 'pg-sas' ).'</td></tr>', 'value'=>'pg-sas-custom'));

		echo '<tr><td><h3>'.__('Inline Images', 'pg-sas' ).'</h3></td></tr>';
		echo $this->settings->input_checkbox($optionset,'inline_show_title',array('before'=>'<tr><td>'.__('Show title when hovering over inline image links', 'pg-sas' ).'</td><td>', 'after'=>'</td></tr>'));
		
		echo "</table>";
	}

	/**
	 * Show the help page content
	 */

	function show_help_settings(){
		
		$output =  '<h3>'.__('PG Simple Affiliate Shop', 'pg-sas').'</h3>';
		$output .= '<p>'.__('This shop allows you to manage a simple affiliate shop on your site. It supports two listed formats, a shop page with details for a product you are promoting and a banner format that is intended to display a column of banners in a sidebar', 'pg-sas').'</p>';
		$output .= '<p>'.__('You can also use the inline format to show individual items anywhere in a post or a page.', 'pg-sas').'</p>';
		$output .= '<p>'.__('Once you have entered details such as the product image and link in the store back end you can add a shortcode to pages and widgets.', 'pg-sas').'</p>';
		$output .= '<p>'.__('You can also choose change the text on buttons, show quotes or no quotes at all, whether banner buttons are displayed, or how to change all the formatting', 'pg-sas').'</p>';
		$output .= '<h4>'.__('Short Code Details', 'pg-sas').'</h4>';
		$output .= '<p>'.__('There is now a helper button in the editor toolbar that will create the shortcodes below for you. You do not need to remember or use the codes below as they will be generated for you by the button and popup screen', 'pg-sas').'<br/>';
		$output .= '<p>'.__('The shortcodes are:', 'pg-sas').'<br/>';
		$output .= sprintf(__('%1$s or %2$s,', 'pg-sas'),'<code>[pg_sas_shop]</code>', '<code>[pg_sas_shop parameters]</code>').'<br/>';
			$output .= sprintf(__('%1$s or %2$s, and', 'pg-sas'),'<code>[pg_sas_banner]</code>', '<code>[pg_sas_banner parameters]</code>') .'<br/>';
			$output .= sprintf(__('%1$s or %2$s,', 'pg-sas'),'<code>[pg_sas_image product_id=??]</code>', '<code>[pg_sas_image product_id=?? parameters]</code>') .'</p>';
		$output .= '<ul>';
			$output .= '<li><code>[pg_sas_shop]</code> '.__('Displays all of the products in the type "shop-product" and their children (ie "shop-products" and "on-sale")', 'pg-sas').'</li>';
			$output .= '<li><code>[pg_sas_shop parameters]</code> '.__('Modifies what is displayed based on the parameter<br/>Valid parameters are:', 'pg-sas');
			$output .= '<ul>';
				$output .= '<li><code>showtitle="false"</code> '.__('Don\'t show the title for the products, default is true', 'pg-sas').'</li>';
				$output .= '<li><code>product_id=??</code> '.__('Put the product number in place of ??. Product number can be found on the All Products page in the Title/# column. This will override the following parameters', 'pg-sas').'</li>';
				$output .= '<li><code>category=??</code> '.__('Put the "slug" of the product category you want to display. Slug can be found on the Product Type page', 'pg-sas').'</li>';
				$output .= '<li><code>showchildren="false"</code> '.__('Do not show children of this category (default is true)', 'pg-sas').'</li>';
				$output .= '<li><code>products_per_page=??</code> '.__('Override the number of products per page on the settings page. -1 = all on one page', 'pg-sas').'</li>';
			$output .= '</ul></li>';
			$output .= '<li><code>[pg_sas_banner]</code> '.__('Displays all of the banners (these are the ones with the type "banner-advert") and their children', 'pg-sas').'</li>';
			$output .= '<li><code>[pg_sas_banner parameters]</code> '.__('Modifies what is displayed based on the parameter<br/>Valid parameters are:', 'pg-sas');
			$output .= '<ul>';
				$output .= '<li><code>product_id=??</code> '.__('Put the product number in place of ??. Product number can be found on the All Products page in the Title/# column. This will override the following parameters', 'pg-sas').'</li>';
				$output .= '<li><code>category=??</code> '.__('Put the "slug" of the product category you want to display. Slug can be found on the Product Type page', 'pg-sas').'</li>';
				$output .= '<li><code>showchildren="false"</code> '.__('Do not show children of this category (default is true)', 'pg-sas').'</li>';
			$output .= '</ul></li>';
			$output .= '<li><code>[pg_sas_image product_id=?? parameters]</code> '.__('Use this in a post or page to show just one image and link<br/>Valid parameters are:', 'pg-sas');
			$output .= '<ul>';
				$output .= '<li><code>product_id=??</code> '.__('Put the product number in place of ??. Product number can be found on the All Products page in the Title/# column', 'pg-sas').'</li>';
				$output .= '<li><code>float=??</code> '.__('put any valid WordPress float instruction here: alignleft, alignright or aligncenter', 'pg-sas').'</li>';
				$output .= '<li><code>height="??px"</code> '.__('specify the height of the image eg height="100px"', 'pg-sas').'</li>';
				$output .= '<li><code>width="??px"</code> '.__('specify the width of the image eg width="100px"', 'pg-sas').'</li>';
			$output .= '</ul></li>';
		$output .= '</ul>';
		$output .= '<p><strong>'.__('For example:', 'pg-sas').' </strong>'.__('To show a shop page that included all the products in the "On Sale" category you would use ', 'pg-sas').'<code>[pg_sas_shop category="on-sale"]</code></p>';
		$output .= '<h4>'.__('Alternate quote images', 'pg-sas').'</h4>';
		$output .= '<p>'.__('If you don\'t want to display the standard quotes then just un-tick the show quotes checkbox', 'pg-sas').'</p>';
		$output .= '<p>'.__('If you are a bit more technical and want to override all of the formatting then copy the pg-sas.css file to your theme folder and overwrite the css that controls the format. This is the safest way as your changes won\'t be lost when the plugin is updated. You will need to add your own formatting if you choose the custom color for buttons', 'pg-sas').'</p>';

		echo $output;
	}

}

/**
 * Activation Code:
 * Check the minimum WordPress version is in use and call the activation function on the main class
 */
function pg_sas_activate(){
	
	$min_version = '3.4.1';
	
	// Check that the minimum version of WordPress is in place
	if ( version_compare ( get_bloginfo( 'version' ), $min_version, '<' ) ) {
		deactivate_plugins(plugin_basename(__FILE__)); // Deactivate 
		wp_die( 
			sprintf(__('Your WordPress version is %1$s, you need to be running at least WordPress %2$s to use PG Simple Affiliate Shop', 'pg-sas' ), get_bloginfo( 'version' ), $min_version),
			__('PG Simple Affiliate Shop requires a later version of WordPress', 'pg_sas'), array('back_link' => true));
	}
	// Minimum is there - let's go!
	$shop = new PGSimpleAffiliateShop();
	$shop->activate();
}

/**
 * Deactivation Code
 * 
 * This deactivation code will not fully remove the permalink structure because init has already run.
 * Any calls to a product page will go to the home page of the site.
 * The final flush_rewrite_rules(); in the uninstall script will remove them fully.
 * 
 * if ( isset($_GET['action']) && 'deactivate' == $_GET['action'] && isset($_GET['plugin']) && plugin_basename( __FILE__ ) == $_GET['plugin']  )
 */
function pg_sas_deactivate(){
	$shop = new PGSimpleAffiliateShop();
	$shop->deactivate();
}
