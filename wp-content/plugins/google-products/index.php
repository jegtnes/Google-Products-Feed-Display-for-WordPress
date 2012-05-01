<?php  
	/* 
	Plugin Name: Google Products Feed Display 
	Plugin URI: https://github.com/jegtnes/Google-Products-Feed-Display-for-WordPress
	Description: A plugin to acquire, parse and display products from a Google Products feed.
	Author: Alexander Jegtnes 
	Version: 0.9
	Author URI: http://jegtnes.co.uk
	--------------------------------
	Thanks to Christian Lupu for this tutorial, which is what most of the WP functionality is inspired by.
	http://net.tutsplus.com/tutorials/wordpress/creating-a-custom-wordpress-plugin-from-scratch/
	*/  


	//includes the widget code
	include_once('GooproWidget.php');

	/**
	 * Ensures the admin page is included
	 */
	function goopro_admin() {  
		include('goopro_admin.php');  
	}  
	
	/**
	 * Creates an option page in the WP admin area 
	 */
	function goopro_admin_init() {  
		add_options_page("Google Products Feed Display: Settings", "Google Products Feed Display", 1, "google_products_feed_display", "goopro_admin");
	}  

	/**
	 * Adds styling to the admin area 
	 */
	function admin_register_head() {
		$siteurl = get_option('siteurl');
		$css = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';
		$js = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/js.js';
		echo "<script type='text/javascript' src='$js'></script>\n";
		echo "<link rel='stylesheet' type='text/css' href='$css' />\n";
	}

	/**
	 * Will be called on installation of the plugin. Creates the table.
	 * @global type $wpdb 
	 */
	function goopro_install() {
		global $wpdb;

		//sets the table name with the appropriate prefix
		$table_name = $wpdb->prefix . "goopro";

		//if the table doesn't already exist, create it
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

			//the pure SQL
			$sql = "CREATE TABLE $table_name (
			gp_id mediumint(9) NOT NULL AUTO_INCREMENT,
			title varchar(200) NOT NULL,
			link varchar(500) NOT NULL,
			image_link varchar(500) NOT NULL,
			price decimal(6,2) NOT NULL,
			PRIMARY KEY  id (gp_id)
			)";  
		}

		//execute the SQL
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option("goopro_cron_interval","daily");
		update_option("goopro_cron_enabled",false);
		
	}
	
	/**
	 * Updates the database with products according to the parameters.
	 * @global type $wpdb The WP database class
	 * @param type $brandname The brand we want to use
	 * @param type $feedurl The XML feed we update the DB from
	 * @return boolean Returns true upon success.
	 */
	
	function goopro_update_products($brandname, $feedurl) {
		global $wpdb;
		$table_name = $wpdb->prefix . "goopro";
		
		//checks for a complete installation
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) { ?>
			<h4>Installation procedure did not happen correctly (this can happen on multi-user blog networks). Attempting to reinstall...</h4>
			
			<?php 
			goopro_install();
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name): ?>
				<h4>Re-installation failed. Please email <a href="mailto:alexander.j@lrindustries.eu">alexander.j@lrindustries.eu</a> with this information and I'll try to help you out.</h4>
				<?php else: ?>
				<h4>Re-installation succeeded. Everything should be running smoothly now!</h4> 
			<?php	endif; 	
		}

		
		//sets defaults
		if (!isset($brandname)) $brandname = get_option("goopro_brandname");
		if (!isset($feedurl)) $feedurl = get_option("goopro_feedurl");
		
		$success = false;
		
		//requires some WordPress database magic stuff
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		$table_name = $wpdb->prefix . "goopro";

		//sets the maximum execution time to 120 seconds (Huge XML files can take a while, yo.)
		set_time_limit(120);

		//the current brand name we're working with
		$brand = (string) $brandname;

		//sets the table name with the appropriate prefix
		$table_name = $wpdb->prefix . "goopro";

		//loads the source
		$sourceurl = simplexml_load_file($feedurl);

		//prepares the start of the SQL statement we're working with
		$sql = "INSERT INTO $table_name VALUES ";

		//checks that the XML file is reachable
		if (!empty($sourceurl)) {

			//loops through the XML file
			foreach($sourceurl->channel->item as $product) {
				//if the current looped item is the right brand, add it
				if (stristr($product->title,$brand) == true) {
					//Uses the namespace linked in the XML document
					$namespaces = $product->getNameSpaces(true);
					$g = $product->children($namespaces['g']);
					
					//sets all vars and safely escapes / casts them.
					$prod_title = (String) mysql_real_escape_string($product->title);
					$prod_link = (String) mysql_real_escape_string($product->link);
					$prod_price = (float) mysql_real_escape_string($g->price);
					$prod_imagelink = (String) mysql_real_escape_string($g->image_link);
					
					//database magic here, this adds to the SQL query
					$sql .= "(
					'NULL',
					'$prod_title',
					'$prod_link',
					'$prod_imagelink',
					'$prod_price'
					), ";
				}

			}
			
			//if there are matching products
			if ($sql != "INSERT INTO $table_name VALUES ") {

				//replaces the last comma of $sql with a semicolon
				//so we get a valid SQL query
				$sql = substr($sql,0,-2) . ";";

				//gets rid of old results
				//can't update current results and insert new ones - as brand names can change
				$wpdb->query("TRUNCATE TABLE `$table_name`;");

				//Execute the SQL query
				$wpdb->query("$sql") or die(mysql_error());

				//sets the time the XML feed was last updated.
				update_option('goopro_lastupdated', time());
				$success = true;
			}
		}
		
		else {
			die("Can't parse XML file!");
		}
		return $success;
	}
    
	/**
	 * Returns the specified number of products (with markup)
	 * @global type $wpdb The WordPress database
	 * @param type $num The number of products to return
	 * @return type
	 */
	function goopro_getproducts($num) {
		
		//requires some WordPress database magic stuff
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;
		$table_name = $wpdb->prefix . "goopro";

		//What to return
		$content = "";
		
		//prepares the SQL statement
		$sql = "SELECT 
		title,
		link,
		image_link,
		price
		FROM $table_name
		ORDER BY gp_id DESC
		LIMIT 0,$num
		;";
		$result = $wpdb->get_results($sql);

		//selects currency to display
		switch(get_option('goopro_currency')) {
			case "pound":
				$currency = "&pound;";
				break;
			case "euro":
				$currency = "&euro;";
				break;
			default:
				$currency = "";
		}

		//outputs the products
		foreach($result as $row) {
			$content .= 
			"<div class=\"goopro_product\">\n
			<a href=\"$row->link\"><h4>$row->title</h4>\n
			<img src=\"$row->image_link\" alt=\"\"/>\n
			<span class=\"price\">$currency$row->price</span></a>\n
			</div>\n";
		}
		
		return $content;
	}
		
	$interval = get_option('goopro_cron_interval');
	if (!wp_next_scheduled('update_frooglefeed') && get_option('goopro_cron_enabled') == true) {
		wp_schedule_event( time(), $interval, 'update_frooglefeed' );
	}
	
	/**
	 * Adds extra WP cron intervals for perusal
	 * @return Array
	 */
	function goopro_extra_cron_intervals() {
				
		$sched['monthly'] = array(
				'interval' => 2629743,
				'display'  => 'Once every month (30.44 days)',
    );
		
		$sched['fortnight'] = array(
				'interval' => 1209600,
				'display'  => 'Once every fortnight',
    );
		
		$sched['weekly'] = array(
				'interval' => 604800,
				'display'  => 'Once weekly',
    );
		
		$sched['6_days'] = array(
				'interval' => 518400,
				'display'  => 'Once every 6 days',
    );
		
		$sched['5_days'] = array(
				'interval' => 432000,
				'display'  => 'Once every 5 days',
    );
		
		$sched['4_days'] = array(
				'interval' => 345600,
				'display'  => 'Once every 4 days',
    );
		
		$sched['3_days'] = array(
				'interval' => 259200,
				'display'  => 'Once every 3 days',
    );
		
		$sched['2_days'] = array(
				'interval' => 172800,
				'display'  => 'Once every 2 days',
    );
		
		$sched['half_hour'] = array(
				'interval' => 1800,
				'display'  => 'Once every half an hour',
    );
		
		$sched['15_minutes'] = array(
				'interval' => 900,
				'display'  => 'Once every 15 minutes',
    );
		
		$sched['minute'] = array(
				'interval' => 60,
				'display'  => 'Once every minute',
    );
		
		return $sched;
	}

	
	/**
	 * Creates a WordPress top-level page
	 * adapted from http://wordpress.org/support/topic/how-do-i-create-a-new-page-with-the-plugin-im-building#post-1341616
	 * thanks, mr. Crossen!
	 */
	function goopro_create_page() {		
		
		$the_page_title = 'Latest Products';
		$the_page_name = 'latest-products';

		// the menu entry
		delete_option("goopro_page_title");
		add_option("goopro_page_title", $the_page_title, '', 'yes');
		
		// the slug
		delete_option("goopro_page_name");
		add_option("goopro_page_name", $the_page_name, '', 'yes');
		
		// the id
		delete_option("goopro_page_id");
		add_option("goopro_page_id", '0', '', 'yes');

		$the_page = get_page_by_title($the_page_title);

		if (!$the_page) {
				// Create post object
				$_p = array();
				$_p['post_title'] = $the_page_title;
				$_p['post_content'] = "Please do not edit, this will be overriden by the plugin";
				$_p['post_status'] = 'publish';
				$_p['post_type'] = 'page';
				$_p['comment_status'] = 'closed';
				$_p['ping_status'] = 'closed';
				$_p['post_category'] = array(1); // the default 'Uncategorised'

				// Insert the post into the database
				$the_page_id = wp_insert_post($_p);
		}
		
		else {
				// the plugin may have been previously active and the page may just be trashed
				$the_page_id = $the_page->ID;
				
				//make sure the page is not trashed
				$the_page->post_status = 'publish';
				$the_page_id = wp_update_post($the_page);
		}

		delete_option("goopro_page_id");
		add_option("goopro_page_id",$the_page_id );
	}
	

	/**
	 * Checks to see whether we're currently accessing the created latest products page
	 * Also adapted from http://wordpress.org/support/topic/how-do-i-create-a-new-page-with-the-plugin-im-building#post-1341616
	 * @param type $q
	 * @return type 
	 */
	function goopro_page_query_parser($q) {
		$the_page_name = get_option( "goopro_page_name" );
		$the_page_id = get_option( "goopro_page_id" );

		//if permalinks are used?
		if (!$q->did_permalink && (isset($q->query_vars['page_id'])) && (intval($q->query_vars['page_id']) == $the_page_id)) {
			$q->set('goopro_page_called', TRUE );
			return $q;
		}
		
		//if permalinks aren't used
		else if(isset($q->query_vars['pagename']) && (($q->query_vars['pagename'] == $the_page_name) OR (strpos($q->query_vars['pagename'],$the_page_name.'/') === 0))) {
			$q->set('goopro_page_called', TRUE );
			return $q;
		}
		
		else {
			$q->set('goopro_page_called', FALSE );
			return $q;
		}

	}
	
	/**
	 * Changes the content of the latest products page if it is called
	 * Also adapted from http://wordpress.org/support/topic/how-do-i-create-a-new-page-with-the-plugin-im-building#post-1341616
	 * @global type $wp_query
	 * @param type $posts
	 * @return type 
	 */
	function goopro_page_filter($posts) {
		global $wp_query;

		//if the Google Products page is called
		if($wp_query->get("goopro_page_called")) {
			//replace title and content with whatever we want
			$posts[0]->post_title = "Latest " . get_option("goopro_brandname") . " products";
			$posts[0]->post_content = goopro_getproducts(get_option(goopro_number));
		}
		
		//and return it
		return $posts;
	}
	
	/**
	 * Removes the latest products page and removes the options associated with them
	 * Also adapted from http://wordpress.org/support/topic/how-do-i-create-a-new-page-with-the-plugin-im-building#post-1341616
	 */
	function goopro_remove_page() {
		
    //the id of our page
    $the_page_id = get_option("goopro_page_id");
		
    if($the_page_id) {
			wp_delete_post($the_page_id); // this will trash, not delete
    }

    delete_option("goopro_page_title");
    delete_option("goopro_page_name");
    delete_option("goopro_page_id");
	}
	
	/**
	 * Registers or unregisters a cron job depending on settings.
	 * This function is hooked to the wp hook. 
	 */
	function register_goopro_cron() {
		$interval = get_option('goopro_cron_interval');
		
		//if cron is enabled and we haven't already added this event
		if (!wp_next_scheduled('update_frooglefeed') && get_option('goopro_cron_enabled') == true) {
			wp_schedule_event( time(), $interval, 'update_frooglefeed' );
		}
		
		//if cron is disabled, clear the schedule
		if (get_option('goopro_cron_enabled') == false) {
			wp_clear_scheduled_hook('update_frooglefeed');
		}
	}
	
	register_activation_hook(__FILE__,'goopro_install');
	
	add_filter('cron_schedules', 'goopro_extra_cron_intervals');
	add_filter('the_posts', 'goopro_page_filter');
	add_filter('parse_query','goopro_page_query_parser');
	
	add_action('wp', 'register_goopro_cron');
	add_action('update_frooglefeed', 'goopro_update_products');
	add_action('admin_head', 'admin_register_head');
	add_action('admin_menu', 'goopro_admin_init');  
?>