<?php
	if(defined('WP_UNINSTALL_PLUGIN')) {  
		global $wpdb;

		//sets the table name with the appropriate prefix
		$table_name = $wpdb->prefix . "goopro";
		
		//drops the google products table
		$sql = "DROP TABLE IF EXISTS $table_name;";
		
		//execute the SQL
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		//removes the page if set up
		goopro_remove_page();
		
		//removes settings
		delete_option('goopro_brandname');  
		delete_option('goopro_number');  
		delete_option('goopro_currency');  
		delete_option('goopro_feedurl');  
		delete_option("goopro_lastupdated");
		delete_option("goopro_cron_interval");
		delete_option("goopro_cron_enabled");
		
		wp_clear_scheduled_hook('update_frooglefeed');
		
		remove_filter('cron_schedules', 'goopro_extra_cron_intervals');
		remove_filter('the_posts', 'goopro_page_filter');
		remove_filter('parse_query','goopro_page_query_parser');
		
		remove_action('wp', 'register_goopro_cron');
		remove_action('update_frooglefeed', 'goopro_update_products');
		remove_action('admin_head', 'admin_register_head');
		remove_action('admin_menu', 'goopro_admin_init');  
	}  
?>
