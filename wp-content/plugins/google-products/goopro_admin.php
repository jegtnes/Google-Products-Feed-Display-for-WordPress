<div class="wrap">
    <div id="icon-tools" class="icon32"></div><h2>Google Products Feed Display Options</h2>
    <form name="goopro_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" class="goopro_form">
        <input type="hidden" name="goopro_hidden" value="Y">
        <h4>Brand settings</h4>
        <ul>
            <li>
                <label for="goopro_brandname">Brand name:</label>
                <input type="text" name="goopro_brandname" id="goopro_brandname" value="<?php echo $brandname?>" size="20" />
            </li>
            <li>
                <label for="goopro_number">Number of products:</label>
                <input type="number" name="goopro_number" id="goopro_brandnumber" value="<?php echo $goopro_number?>" size="20" />
            </li>
            <li>
                <label for="goopro_currency">Currency:</label>
                <select name="goopro_currency" id="goopro_currency">
                    <option name="goopro_currency_pound">Pound Sterling (&pound;)</option>
                    <option name="goopro_currency_euro">Euro (&euro;)</option>
                </select>
            </li>
            <li>
                <label for="goopro_countrycode">Country code:</label>
                <input type="text" name="goopro_countrycode" id="goopro_countrycode" value="<?php echo $goopro_countrycode?>" size="20" />
            </li>
        </ul>
        <hr />
        <h4>Database Settings</h4>
        <!--<p>Database host: <input type="text" name="goopro_dbhost" value="<?php echo $dbpwd; ?>" size="20">e.g.: localhost</p>
        <p>Database name: <input type="text" name="goopro_dbname" value="<?php echo $dbpwd; ?>" size="20">e.g.: productsdb</p>
        <p>Database user: <input type="text" name="goopro_dbuser" value="<?php echo $dbpwd; ?>" size="20">e.g.: root</p>
        <p>Database password: <input type="text" name="goopro_dbpassword" value="<?php echo $dbpwd; ?>" size="20">e.g.: secretpassword</p>-->
    </form>
    
</div>