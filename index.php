<?php
/*
Plugin Name: Gold Feed Live
Plugin URI: http://wordpress.org/extend/plugins/gold-price-live
Description: Easily place the spot gold, silver, platinum and/or palladium price on your blog or website using shortcode. The price is per troy ounce and is in USD and is available in all currencies. This FREE plugin updates metals pricing 2 times per day. Please subscribe at www.gold-feed.com for real time pricing. <strong>All you need to do is install the plugin to your Wordpress, activate the plugin and then use the shortcode [gold], [silver], [platinum] or [palladium]</strong>. Horray! The price will be output! This works in pages, posts and widgets. If you have any questions or require assistance email <a href="mailto:info@gold-feed.com">info@gold-feed.com</a>.
Version: 3.3
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
    <h1 align="center" class="style1 style4"><span>THANK YOU FOR INSTALLING </span></h1>
<h1 align="center" class="style1"><span  style="color: #C00">(FREE) Gold Feed Live!</span> </h1>
<p align="center">Our FREE plugin updates precious metals pricing 2 times per day. In the am &amp; pm. </p>
<p align="center"><em><strong>We also have a <a href="http://gold-feed.com/wordpress-gold-price-live-plugin.php" style="text-decoration:none; ">paid version</a>. See bottom of this page for more info and to subscribe. </strong></em></p>
<h2 align="center"> To add the value of Gold, Silver, Platinum and Palladium just write: </h2>
<h4 align="center">
  <pre>[gold], [silver], [platinum] or [palladium]</pre>
</h4>
<h4 align="center">In any of your Wordpress pages, posts or widgets.</h4>
<hr>
<h3 align="center">OUR PAID VERSION FEATURES </h3>
<p align="center">** Real Time Pricing ** </p>
<center><div style="width:325px; ">
  <p>Bid, Ask, High, Low, Dollar Change, Percent Change, 1st London Fix, 2nd London Fix, <a href="http://gold-feed.com/wordpress-gold-price-live-plugin.php" style="text-decoration:none; ">More...</a> </p>
</div>
</center>
<p align="center"><a href="http://gold-feed.com/wordpress-gold-price-live-plugin.php" target="_blank">CLICK HERE TO SUBSCRIBE NOW</a></p>
<hr>

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
