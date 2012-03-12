<?php  
    /* 
    Plugin Name: Google Products Feed Display 
    Plugin URI: https://github.com/jegtnes/Google-Products-Feed-Display-for-WordPress
    Description: A plugin to acquire, parse and display products from a Google Products feed.
    Author: Alexander Jegtnes 
    Version: 0.1
    Author URI: http://jegtnes.co.uk
    --------------------------------
    Thanks to Christian Lupu for this tutorial, which is what most of the WP functionality is inspired by.
    http://net.tutsplus.com/tutorials/wordpress/creating-a-custom-wordpress-plugin-from-scratch/
    */  
    
    function goopro_admin() {  
        include('goopro_admin.php');  
    }  

    function goopro_admin_init() {  
        add_options_page("Google Products Feed Display: Settings", "Google Products Feed Display", 1, "google_products_feed_display", "goopro_admin");
    }  
    
    function admin_register_head() {
        $siteurl = get_option('siteurl');
        $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';
        echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    }
    
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
            description varchar(500),
            link varchar(500) NOT NULL,
            image_link varchar(500) NOT NULL,
            price decimal(6,2) NOT NULL,
            PRIMARY KEY  id (gp_id)
            )";  
        }

        //execute the SQL
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    //TODO: Reverse the results
    function goopro_updateProducts() {
        //sets the maximum execution time to 120 seconds (Huge XML files can take a while, yo.)
        set_time_limit(120);
        
        $brand = (string) get_option("goopro_brandname");
        
        $count = 0;
        $sourceurl = simplexml_load_file(get_option("goopro_feedurl"));
        
        foreach ($sourceurl->channel->item as $item) {
            $source[]=$item;
        }
        
        $source = array_reverse($source);
        
        //database magic
        $sql = "";
        global $wpdb;
        $table_name = $wpdb->prefix . "goopro";
        
        //checks that the XML file is reachable
        if (!empty($source)) {
            foreach($source as $product) {

                if ($count < get_option('goopro_number')) {

                    //If the current product matches the brand we've specified
                    //(I'm sure there's a much faster way to do this than these five million nested loops and if statements)
                    //(but I can't quite figure out how at the moment)
                    if (stristr($product->title,$brand) == true) {
                        $count++;

                        //Uses the namespace linked in the XML document
                        $namespaces = $product->getNameSpaces(true);
                        $g = $product->children($namespaces['g']);

                        //sets all vars and safely escapes / casts them.
                        $prod_title = (String) mysql_real_escape_string($product->title);
                        $prod_desc = (String) mysql_real_escape_string($product->description);
                        $prod_link = (String) mysql_real_escape_string($product->link);
                        $prod_price = (float) mysql_real_escape_string($g->price);
                        $prod_imagelink = (String) mysql_real_escape_string($g->image_link);

                        //database magic here, this prepares the SQL query
                        $sql .= "INSERT INTO wp_goopro VALUES (
                        'NULL',
                        '$prod_title',
                        '$prod_desc',
                        '$prod_link',
                        '$prod_imagelink',
                        $prod_price
                        );";
                    }
                }

                else {
                break;
                } 
            }

            //database magic here, this executes the SQL query and gets rid of old results
            $wpdb->query("TRUNCATE TABLE `$table_name`;");
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            //sets the time the XML feed was last updated.
            update_option('goopro_lastupdated', time());
            }
            
        else {
            echo "Can't parse XML file!";
        }
    }
    
    function goopro_getproducts() {
        echo "<div class=\"goopro_display\">";
        echo "</div>";
    }
    
    register_activation_hook(__FILE__,'goopro_install');
    add_action('admin_head', 'admin_register_head');
    add_action('admin_menu', 'goopro_admin_init');  
?>