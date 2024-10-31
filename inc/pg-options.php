<?php
/*
http://www.peoplesgeek.com/plugins

	Copyright 2012 Brian Reddick (info@peoplesgeek.com)

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

/**
 * Standard class to help out with things like options pages and setup
 */
class PG_OptionsSupport{
	
	private $option_pages;
	private $plugin_name;
	private $is_plugin;
	private $posttype;
	private $slug;

	/**
	 * Constructor for option pages and menu support
	 * @param $name Name of the Plugin defaults to 'PG Plugin'
	 * @param $is_plugin string Is this a plugin (vs a theme)
	 * @param $slug string The base of the slug for menus etc defaults to pg-
	 * @param $posttype string If this is a post type menu then provide the post type
	 * @param $option_pages array Options page details, names, titles, default page etc
	 */
	function PG_OptionsSupport($name = 'PG Plugin', $is_plugin = true, $slug='pg-', $posttype = '', $option_pages = array()){
		$this->option_pages = wp_parse_args( $option_pages, array('pages' => array('general'=> array( 'title'=>'General Options', 	'fields' => array( $this , 'form_inputs1')),
																					'help' => array( 'title'=>'Help', 				'fields' => array( $this , 'form_inputs2'))
																				),
																'default' => 'general'
																));
		$this->plugin_name = $name;
		$this->$is_plugin = $is_plugin;
		$this->posttype = $posttype;
		$this->slug = $slug;
		
		add_action('admin_menu', array( $this , 'add_options_page'), 11);
		add_action('admin_init', array( $this ,'register_options') );					
	}
	
	
	function register_options(){
		
		foreach ($this->option_pages['pages'] as $page => $value) {
			
			$option_set_name = $this->optionset_name( $page);

			register_setting ( $option_set_name, $option_set_name );
			
			add_settings_section(	$this->slug.'-'.$page, 
									$value['title'], 
									( isset($value['section_title'])? $value['section_title']: array( $this , 'blank_heading_text')),
									$option_set_name);
			
			add_settings_field(		'show-'.$this->slug.'-'.$page, 
									( isset($value['field_title'])? $value['field_title']:'' ),
									$value['fields'],
									$option_set_name, 
									$this->slug.'-'.$page,
									( isset($value['field_args'])? $value['field_args']:array() ));
		}
	}
	
	function optionset_name($page='help'){
		return $this->slug.'-'.$page.'-opt';
	}
	
	function blank_heading_text (){
		echo "";
	}
	
	function form_inputs($arg=null){
		echo "outputting form fields";
	}
	function form_inputs1($arg=null){
		echo "outputting form fields 1";
		$option_set_name = $this->optionset_name('general');
		echo $this->input_text( $option_set_name ,'a-field');
	}
	function form_inputs2($arg=null){
		echo "outputting form fields 2";
	}
	

	function add_options_page(){
		if ($this->posttype != '') {
			add_submenu_page( "edit.php?post_type={$this->posttype}",
				$this->plugin_name . ' Options',
				'Settings',
				'edit_pages', 
				$this->slug,
				array( $this ,'show_options_page') );
		} else {
			add_theme_page(	$this->plugin_name . 'Options', 
							$this->plugin_name . 'Options', 
							'manage_options',
							$this->slug , 
							array( $this ,'show_options_page') );
		}
	
		
	}
	
	/**
	 * Display the options page
	 */
	function show_options_page(){
	?> 	
	<div id="pg-settings" class="wrap">
		<?php screen_icon(); 
		echo "<h2>" . $this->plugin_name ."</h2>\n";
		settings_errors(); 	  
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : "{$this->option_pages['default']}"; ?>
		<h2 class="nav-tab-wrapper">  
		<?php foreach ($this->option_pages['pages'] as $page => $value) {
			$active_class = ( $active_tab == "{$page}" ? "nav-tab-active" : "");
			echo "<a href='" . add_query_arg('tab',$page) . "' class='nav-tab {$active_class}'>{$value['title']}</a> ";
		}
		?>
		</h2>  
		<form action="options.php" method="post">
			<?php 	
			if ($active_tab == 'help'){
				do_settings_sections( ($this->slug .'-help-opt') );
			} else {
				settings_fields($this->optionset_name( $active_tab ));
				do_settings_sections($this->optionset_name( $active_tab ));
				echo '<input name="Submit" type="submit" value="Save Changes" />';
			}
			?>
		</form>
	</div>
	<?php 
}

/* ======== Helper functons for field displays ========*/

	/**
	 * Helper for outputting an input checkbox on a settings page
	 * @param $option_set string The name of the option set
	 * @param $option_name string The name of the option to be stored
	 * @param $atts array before, after, disabled
	 */
	function input_checkbox($option_set='', $option_name='', $atts=''){
		extract( shortcode_atts( array ( 'before' => '<td>', 'after' => '</td>', 'label' => '', 'disabled' => false ), $atts ) );
		$options = get_option( $option_set );
		$selected = isset( $options[$option_name])? "checked='yes'" :'';
		if ( $disabled ) {
			$output  = "$before<input id='$option_name' name='".$option_set."HIDDEN[$option_name]' type='checkbox' disabled=true $selected />$after";
			$output .= "<input id='$option_name' name='".$option_set."[$option_name]' type='hidden' $selected />";
		}else{
			$output  = "$before<input id='$option_name' name='".$option_set."[$option_name]' type='checkbox' $selected />$after";
		}
		
		return $output;
	}
	
	/**
	 * Helper for outputting an input radio button on a settings page
	 * @param $option_set string The name of the option set
	 * @param $option_name string The name of the option to be stored
	 * @param $atts array before, after, disabled
	 */
	function input_radiobutton($option_set='', $option_name='', $atts=''){
		extract( shortcode_atts( array ( 'before' => '<td>', 'after' => '</td>', 'value'=>'', 'disabled' => false ), $atts ) );
		$options = get_option( $option_set );
		$selected = (isset( $options[$option_name] ) && $value == $options[$option_name])? "checked='yes'" :'';
		if ( $disabled ) {
			$output  = "$before<input id='$option_name' name='".$option_set."HIDDEN[$option_name]' type='radio' disabled=true $selected value='$value' />$after";
			$output .= "<input id='$option_name' name='".$option_set."[$option_name]' type='hidden' $selected />";
		}else{
			$output  = "$before<input id='$option_name' name='".$option_set."[$option_name]' type='radio' $selected value='$value'/>$after";
		}
		
		return $output;
	}
//	<input type="radio" name="sex" value="male" checked="Y" >Male<br>
//<input type="radio" name="sex" value="female">Female
	
	/**
	 * Helper for outputting an input text field on a settings page
	 * @param $option_set string The name of the option set
	 * @param $option_name string The name of the option to be stored
	 * @param $atts array before, after, label, items(array)
	 */
	function input_listbox($option_set='', $option_name='', $atts=''){
		extract( shortcode_atts( array ( 'before' => '<td>', 'after' => '</td>', 'label' => '', 'items' => ARRAY_A ), $atts ) );
		$options = get_option( $option_set );
		$value = isset( $options[$option_name])? $options[$option_name] : '';
		$output = $before . "<select name='".$option_set."[$option_name]'>";
		foreach ($items as $key => $listItem) {
			$selected = ($key == $value)? "selected='selected'" : "";
			$output .=  "<option value='$key' $selected>$listItem</option>";
		}
		$output .= "</select>" .$after;
	
		
		return $output;
	}
	
	/**
	 * Helper for outputting an input text field on a settings page
	 * @param $option_set string The name of the option set
	 * @param $option_name string The name of the option to be stored
	 * @param $atts array before, after, char, label
	 */
	function input_text($option_set='', $option_name='', $atts = ''){
		extract( shortcode_atts( array ( 'before' => '<td>', 'after' => '</td>', 'char' => '', 'label' => '' ), $atts ) );
		$options = get_option( $option_set );
		$value = isset( $options[$option_name])? $options[$option_name] : '';
		$output = "$before<input id='$option_name' name='".$option_set."[$option_name]' type='text' value='$value'";
		$output .= ($char == '') ? "": "size='$char'";
		$output .= ($label == '') ? '': "";
		$output .= " />$after";
		
		return $output;
	}
	
	/**
	 * Helper for outputting an input text field on a settings page
	 * @param $option_set string The name of the option set
	 * @param $option_name string The name of the option to be stored
	 * @param $atts array before, after, rows, cols
	 */
	function input_textarea($option_set='', $option_name='', $atts = ''){
		extract( shortcode_atts( array ( 'before' => '<td>', 'after' => '</td>', 'rows' => '5', 'cols' => '100' ), $atts ) );
		$options = get_option( $option_set );
		$value = isset( $options[$option_name])? $options[$option_name] : '';
		//$value ="barry";
		$output = "$before<textarea id='$option_name' name='".$option_set."[$option_name]' type='text' ";
		$output .= ($rows == '') ? "": " rows='$rows'";
		$output .= ($cols == '') ? '': " cols='$cols'";
		$output .= " >$value</textarea>$after";
		
		return $output;
	}


}