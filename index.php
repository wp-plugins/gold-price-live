<?php
/*
Plugin Name: Gold Feed Live
Plugin URI: http://wordpress.org/extend/plugins/gold-price-live
Description: Easily place the spot gold, silver, platinum and/or palladium price on your blog or website using shortcode. The price is per troy ounce and is in USD and is available in all currencies. Price output is never more than $0.02 different than the Kitco bid price of gold, silver, platinum and palladium. <strong>All you need to do is install the plugin to your Wordpress, activate the plugin and then use the shortcode [gold], [silver], [platinum] or [palladium]</strong>. Horray! The price will be output! This works in pages, posts and widgets. If you have any questions or require assistance email <a href="mailto:info@gold-feed.com">info@gold-feed.com</a>.
Version: 3.1
Author: Gold Feed Inc.
Author URI: https://gold-feed.com
*/

/*
	Copyright 2013 Gold Feed Inc.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//*************** Admin function ***************
register_activation_hook(__FILE__, 'my_plugin_activate');
add_action('admin_init', 'my_plugin_redirect');

function my_plugin_activate() {
    add_option('my_plugin_do_activation_redirect', true);
}

function my_plugin_redirect() {
    if (get_option('my_plugin_do_activation_redirect', false)) {
        delete_option('my_plugin_do_activation_redirect');
    
		
		
		 wp_redirect( 'options-general.php?page=Gold_Price_Live'); 
    }
}


function oscimp_admin() {

    echo "<h2>" . __( 'Gold Price Live', 'oscimp_trdom' ) . "</h2>"; 
	?>
    <hr/>
    
    <table width="100%" border="0">
  <tr>
    <td width="39%" valign="top"><p><strong><br />
    <span  style="color: #C00; font-size: 20px">Thank you for installing Gold Feed Live!</span></strong> </p>
    <p>Never more than $0.02 cents per gram different than Kitco.</p>
<p><a href="javascript:if(window.print)window.print()">Print this page</a> for your records.</p>
    <ul>
  <li> 
    <h2> To add the bid value of Gold  just write: 
    </h2>
    <h4>
      <pre>[gold] or [gold_feeder]</pre>
      In any of your Wordpress pages, posts or widgets.</h4>
  </li>
   <li> 
     <h2> To add the bid value of Silver  just write: 
     </h2>
     <h4>
       <pre>[silver]</pre>
     In any of your Wordpress pages, posts or widgets.</h4>
   </li>
    <li>
      <h2> To add the bid value of Platinum  just write:</h2>
      <pre>[platinum]</pre>
        In any of your Wordpress pages, posts or widgets.
    </li>
     <li>
       <h2> To add the bid value of Palladium  just write:</h2>
       <pre>[palladium]</pre>
       <strong>In any of your Wordpress pages, posts or widgets. </strong></li>
     </ul>
    <p><span style="color: #C00"><strong>Need bid and ask? Email us at</strong></span><strong> <a href="info@gold-feed.com">info@gold-feed.com</a></strong>.</p></td>
    <td width="61%" align="center" valign="middle"><p><strong><span style="color: #000; font-size: 23px; text-align: center;">DO YOU NEED <u>LIVE</u> PRECIOUS METALS PRICING<BR> FOR YOUR WORDPRESS eCOMMERCE PLUGIN?</span></strong></p>
      <p><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="9Z2WGV9YDKG3A">
<table>
<tr><td align="center"><input type="hidden" name="on0" value="Live Pricing Shopping Cart Integration">
  <strong>Live Pricing Shopping Cart Integration</strong></td></tr><tr><td><select name="os0">
	<option value="WooCommerce">WooCommerce : $40.00 CAD - monthly</option>
	<option value="WooCommerce">WooCommerce : $300.00 CAD - yearly</option>
	<option value="WP E-Commerce">WP E-Commerce : $45.00 CAD - monthly</option>
	<option value="WP E-Commerce">WP E-Commerce : $330.00 CAD - yearly</option>
	<option value="Cart66">Cart66 : $20.00 CAD - monthly</option>
	<option value="Cart66">Cart66 : $175.00 CAD - yearly</option>
	<option value="eShop">eShop : $45.00 CAD - monthly</option>
	<option value="eShop">eShop : $330.00 CAD - yearly</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="CAD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">&nbsp;</p>
      </form>
      <p><strong style="font-size: 15px">YOU WILL RECEIVE YOUR eCOMMERCE PLUGIN UPGRADE IMMEDIATLY AFTER <br>SUCCESSFUL PAYMENT. INSTALLATION INSTRUCTIONS WILL ALSO BE PROVIDED.</strong></p>
      <p><strong>Even inexperienced programers can integrate our real time pricing into their current Wordpress <br>eCommerce plugins in less than 10 minutes. We also provide free support if you have an issue.</strong></p>
      <p>• <span style="font-size: 14px; font-family: 'Gill Sans', 'Gill Sans MT', 'Myriad Pro', 'DejaVu Sans Condensed', Helvetica, Arial, sans-serif; color: #F00;"><em><strong>No re-adding your products. Just enter the weights and karat and off you go! Real time pricing.</strong></em><strong></strong></span></p>
      <p><strong style="color: #C00; font-size: 15px;">Our API integrates easily with all of the major Wordpress eCommerce plugins.<br>
    <span style="text-align: center; color: #666;"><em>(Perfect for real time pricing of your for sale online precious metal items.)</em></span></strong></p></td>
  </tr>
</table>

    <?php

}


function oscimp_admin_actions() {
	

    //add_options_page("OSCommerce Product Display", "OSCommerce Product Display", 1, "OSCommerce Product Display", "oscimp_admin");

add_options_page('Gold Price Live', 'Gold_Price_Live', 'manage_options', 'Gold_Price_Live', 'oscimp_admin'); 

	}



add_action('admin_menu', 'oscimp_admin_actions');

//-------------------
function get_gold($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/paid/1f5d5edf5f6re98e8w4d56ew/1f5d5edf5f6re98e8w4d56ewgold.php');

	return $a;
}
 add_shortcode('gold', 'get_gold');
 
 //-------------------
function get_goldfeeder($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/paid/1f5d5edf5f6re98e8w4d56ew/1f5d5edf5f6re98e8w4d56ewgold.php');

	return $a;
}
 add_shortcode('gold_feeder', 'get_goldfeeder');



//----------------------------------------

function get_silver($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/paid/1f5d5edf5f6re98e8w4d56ew/1f5d5edf5f6re98e8w4d56ewsilver.php');
	return $a;
}
 add_shortcode('silver', 'get_silver');
//-----------------------------------------------------


//-------------------
function get_platinum($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/paid/1f5d5edf5f6re98e8w4d56ew/1f5d5edf5f6re98e8w4d56ewplatinum.php');

	return $a;
}
 add_shortcode('platinum', 'get_platinum');



//-------------------------------



function get_palladium($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/paid/1f5d5edf5f6re98e8w4d56ew/1f5d5edf5f6re98e8w4d56ewpalladium.php');

	return $a;
}
 add_shortcode('palladium', 'get_palladium');
add_filter('widget_text', 'do_shortcode');

?>
