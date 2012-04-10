<?php
if($_POST['goopro_hidden'] == 'Y') {  

	$goopro_brandname = $_POST['goopro_brandname'];  
	$goopro_number = $_POST['goopro_number'];  
	$goopro_currency = $_POST['goopro_currency'];  
	$goopro_feedurl = $_POST['goopro_feedurl'];
	$goopro_cron_interval = $_POST['goopro_update_interval'];
	
	if(!isset($_POST['goopro_cron_enabled'])) {
		$goopro_cron_enabled = false;
	}
	else $goopro_cron_enabled = true;	

	if (goopro_update_products($goopro_brandname,$goopro_feedurl) == true) {
			$goopro_lastupdated = get_option("goopro_lastupdated");
			goopro_create_page();
			update_option('goopro_brandname', $goopro_brandname);
			update_option('goopro_number', $goopro_number);
			update_option('goopro_currency', $goopro_currency);
			update_option('goopro_feedurl', $goopro_feedurl);
			update_option('goopro_cron_interval',$goopro_cron_interval);
			update_option('goopro_cron_enabled',$goopro_cron_enabled);
	?>
	<div class="updated">
		<p>
			<strong><?php _e('Options saved &amp; feed updated.' ); ?></strong>
		</p>
	</div>

	<?php 
	}
	
	else { 
	?>
		<div class="error">
			<p>
				<strong><?php _e("No \"$goopro_brandname\" products found, can't update feed settings."); ?></strong>
			</p>
		</div>
		<?php
	}
}

else if($_POST['goopro_update_hidden'] == 'Y') {
	$goopro_brandname = get_option('goopro_brandname');  
	$goopro_number = get_option('goopro_number');  
	$goopro_currency = get_option('goopro_currency');  
	$goopro_feedurl = get_option('goopro_feedurl');   
	goopro_update_products($goopro_brandname,$goopro_feedurl);
	$goopro_lastupdated = get_option("goopro_lastupdated");
	goopro_create_page();
	
	?>
	<div class="updated">
		<p>
			<strong><?php _e('Feed updated.' ); ?></strong>
		</p>
	</div>
	<?php

}

else {
	$goopro_brandname = get_option('goopro_brandname');  
	$goopro_number = get_option('goopro_number');  
	$goopro_currency = get_option('goopro_currency');  
	$goopro_feedurl = get_option('goopro_feedurl');  
	$goopro_lastupdated = get_option("goopro_lastupdated");
} 
?>

<div class="wrap">
	<div id="icon-tools" class="icon32"></div><h2>Google Products Feed Display Options</h2>
	<form name="goopro_updateform" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" class="goopro_form">
		<input type="hidden" name="goopro_update_hidden" value="Y">
		<p class="submit">  
		<input type="submit" name="Submit" value="<?php _e('Update Feed', 'goopro_upopt' ) ?>" />  
		</p>
	</form>
	
	<?php if ($goopro_lastupdated) echo "<h4>Master feed last updated at " . date("G:i:s jS F Y",$goopro_lastupdated) . "</h4>"?>
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
		</ul>
		
		<h4>Automatic update</h4>
		<ul>
			<li>
				<label for="goopro_cron_enabled">Update feed automatically?</label>
				<input type="checkbox" name="goopro_cron_enabled" id="goopro_cron_enabled" <?php if ($goopro_cron_enabled == true) echo "checked=\"checked\""?> />
			</li>
			
			<li>
				<label for="goopro_update_interval">Update interval</label>
				<select id="goopro_update_interval" name="goopro_update_interval">
				<?php 
					foreach(wp_get_schedules() as $k => $v) {
						?><option value="<?php echo $k?>" <?php if ($k == get_option('goopro_cron_interval')) echo "selected=\"selected\""?>>
							<?php echo $v['display']?>
						</option>
					<?php
					} 
				?>
				</select>
			</li>
		</ul>
		<p class="submit">  
		<input type="submit" name="Submit" value="<?php _e('Update Options &amp; Feed', 'goopro_upopt' ) ?>" />  
		</p>
	</form>
	
</div>