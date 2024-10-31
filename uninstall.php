<?php

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();
    
// Remove the rewrite rules - this is the final one and product pages will now '404' instead of divert to home page
flush_rewrite_rules();

// Remove the settings option groups
foreach ( array('pg-sas-general-opt', 'pg-sas-help-opt') as $option) {
	delete_option( $option );
}

//TODO: Delete Product data -add optional delete along with export and import so products can be backed up and moved
//TODO: Delete all images
//TODO: Delete Taxonomy entries