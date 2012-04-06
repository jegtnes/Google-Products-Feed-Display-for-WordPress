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

    //includes the widget code
    include_once('GooproWidget.php');
    
    function goopro_admin_init() {  
        add_options_page("Google Products Feed Display: Settings", "Google Products Feed Display", 1, "google_products_feed_display", "goopro_admin");
    }  
    
    //adds styling to the admin page
    function admin_register_head() {
        $siteurl = get_option('siteurl');
        $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';
        echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    }
    
    //this will be called on installation of the plugin
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
    
    function goopro_updateProducts() {
        //requires some WordPress database magic stuff
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        global $wpdb;
        $table_name = $wpdb->prefix . "goopro";
        
        //sets the maximum execution time to 120 seconds (Huge XML files can take a while, yo.)
        set_time_limit(120);
        
        //the current brand name we're working with
        $brand = (string) get_option("goopro_brandname");
               
        //sets the table name with the appropriate prefix
        $table_name = $wpdb->prefix . "goopro";
        
        //loads the source
        $sourceurl = simplexml_load_file(get_option("goopro_feedurl"));
        
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
                    $prod_desc = (String) mysql_real_escape_string($product->description);
                    $prod_link = (String) mysql_real_escape_string($product->link);
                    $prod_price = (float) mysql_real_escape_string($g->price);
                    $prod_imagelink = (String) mysql_real_escape_string($g->image_link);

                    //database magic here, this adds to the SQL query
                    $sql .= "(
                    'NULL',
                    '$prod_title',
                    '$prod_desc',
                    '$prod_link',
                    '$prod_imagelink',
                    $prod_price
                    ), ";
                }
                
            }
            
            //replaces the last comma of $sql with a semicolon
            //so we get a valid SQL query
            $sql = substr($sql,0,-2) . ";";
            
            //gets rid of old results
            //can't update current results and insert new ones - as brand names can change
            $wpdb->query("TRUNCATE TABLE `$table_name`;");
            
            //Execute the SQL query
            dbDelta($sql);

            //sets the time the XML feed was last updated.
            update_option('goopro_lastupdated', time());
        }
            
        else {
            die("Can't parse XML file!");
        }
    }
    
    function goopro_getproducts($num) {
        //requires some WordPress database magic stuff
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        global $wpdb;
        $table_name = $wpdb->prefix . "goopro";
        
        //prepares the SQL statement
        $sql = "SELECT 
        title,
        link,
        image_link,
        price
        FROM $table_name
        LIMIT 0,$num;";
        $result = $wpdb->get_results($sql);
        
        //selects currency to display
        $currency = "";
        if (get_option('goopro_currency') == 'pound') {
            $currency = "&pound;";
        }
        
        else if (get_option('goopro_currency') == 'euro') {
            $currency = "&euro;";
        }
        
        //outputs the products
        foreach($result as $row) {?>
            <div class="goopro_product">
            <h4><a href="<?php echo $row->link?>"><?php echo "$row->title";?></a></h4>
            <img src="<?php echo $row->image_link; ?>" alt=""/>
            <span class="price"><?php echo $currency . $row->price;?></span>
            </div>

            <?php
        }
    }
    
    register_activation_hook(__FILE__,'goopro_install');
    add_action('admin_head', 'admin_register_head');
    add_action('admin_menu', 'goopro_admin_init');  
?>