<?php
if($_POST['goopro_hidden'] == 'Y') {  
    
    $goopro_brandname = $_POST['goopro_brandname'];  
    update_option('goopro_brandname', $goopro_brandname);
    
    $goopro_number = $_POST['goopro_number'];  
    update_option('goopro_number', $goopro_number);
    
    $goopro_currency = $_POST['goopro_currency'];  
    update_option('goopro_currency', $goopro_currency);
    
    $goopro_feedurl = $_POST['goopro_feedurl'];  
    update_option('goopro_feedurl', $goopro_feedurl);
    
    $goopro_countrycode = $_POST['goopro_countrycode'];  
    update_option('goopro_countrycode', $goopro_countrycode);
    ?>
    <div class="updated">
        <p>
            <strong><?php _e('Options saved.' ); ?></strong>
        </p>
    </div>

    <?php 
}

else {
    $goopro_brandname = get_option('goopro_brandname');  
    $goopro_number = get_option('goopro_number');  
    $goopro_currency = get_option('goopro_currency');  
    $goopro_feedurl = get_option('goopro_feedurl');  
    $goopro_countrycode = get_option('goopro_countrycode');  
} 
?>

<div class="wrap">
    <div id="icon-tools" class="icon32"></div><h2>Google Products Feed Display Options</h2>
    <form name="goopro_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" class="goopro_form">
        <input type="hidden" name="goopro_hidden" value="Y">
        <h4>General settings</h4>
        <ul>
            <li>
                <label for="goopro_brandname">Brand name:</label>
                <input type="text" name="goopro_brandname" id="goopro_brandname" value="<?php echo $goopro_brandname?>" />
            </li>
            <li>
                <label for="goopro_number">Number of products:</label>
                <input type="text" name="goopro_number" id="goopro_brandnumber" value="<?php echo $goopro_number?>"/>
            </li>
            <li>
                <label for="goopro_currency">Currency:</label>
                <select name="goopro_currency" id="goopro_currency">
                    <option name="goopro_currency_pound" value="pound" 
                        <?php //makes sure to select the chosen currency
                        if ($goopro_currency == "pound") echo "selected=\"selected\""?>>
                        Pound Sterling (&pound;)
                    </option>
                    <option name="goopro_currency_euro" value="euro"
                        <?php //makes sure to select the chosen currency
                        if ($goopro_currency == "euro") echo "selected=\"selected\""?>>
                        Euro (&euro;)
                    </option>
                </select>
            </li>
            <li>
                <label for="goopro_feedurl">Feed URL</label>
                <input type="text" name="goopro_feedurl" id="goopro_feedurl" value="<?php echo $goopro_feedurl?>"/>
            </li>
            <li>
                <label for="goopro_countrycode">Feed country code:</label>
                <input type="text" name="goopro_countrycode" id="goopro_countrycode" value="<?php echo $goopro_countrycode?>"/>
            </li>
        </ul>
        
        <p class="submit">  
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'oscimp_trdom' ) ?>" />  
        </p>
    </form>
    
</div>