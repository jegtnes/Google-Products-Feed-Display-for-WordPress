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
    
    function goopro_convertxml($targeturl) {
        //sets the maximum execution time to 100 seconds (XML files can take a while, yo.)
        set_time_limit(100);
        
        //starts tracking time - for debugging purposes
        $time_start = microtime(true);
        
        $brand = (string) get_option("goopro_brandname");
        
        $count = 0;
        $sourceurl = simplexml_load_file(get_option("goopro_feedurl"));
        
        foreach($sourceurl->channel->item as $product) {
              
             if ($count < get_option('goopro_number')) {
                 if (stristr($product->title,$brand) == true) {
                    $count++;
                    echo ("<p>$count products so far. <br />" . $product->title . ", " . $product->description . ", " . $product->link . "<br />" . var_dump($product) . "</p>");
                }
            }
            
            else {
               break;
            } 
        }
        
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo '<b>Total Execution Time:</b> '.$execution_time.' seconds'; //execution time of the script
        update_option('goopro_lastupdated', time());
    }
    
    function goopro_getxml() {
        $ret = array();
        //gets the products
        $products = new SimpleXMLElement(get_option(goopro_feedurl), null, true);
        foreach ($products as $product) {
            //creates a 2-level nested array with each product inside an associative array
            $ret[] = array("title" => $product->title, "link" => $product->link, "image_link" => $product->image_link, "price" => $product->price);
        }
        
        return $ret;
   }
    
    function goopro_getproducts() {
        echo "<div class=\"goopro_display\">";
        
        foreach(goopro_getxml() as $product) {
          /*  echo(   "<h3><a href=\"" . 
                    $product['link'] .
                    "\">" .
                    $product['title'] . 
                    "</a></h3>" .
                    "<img alt=\"" . 
                    $product['title'] .
                    "\" src=\"" . 
                    $product['image_link'] .
                    "\">" .
                    "<h4><a href=\"" . 
                    $product['link'] .
                    "\">" .
                    $product['price'] .
                    "</a></h4>"
                    ); */
            goopro_convertxml("");
        }
        echo "</div>";
    }

    add_action('admin_head', 'admin_register_head');
    add_action('admin_menu', 'goopro_admin_init');  
?>