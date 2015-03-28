<?php
/*
Plugin Name: Gold Feed Live
Plugin URI: http://wordpress.org/extend/plugins/gold-price-live
Description: Easily place the spot gold, silver, platinum and/or palladium price and charts on your blog or website using shortcode. The price is per troy ounce and is in USD and is available in all currencies. This FREE plugin updates metals pricing 2 times per day. Please subscribe at www.gold-feed.com for real time pricing. <strong>All you need to do is install the plugin to your Wordpress, activate the plugin and then use the shortcode [gold_bid], [silver_bid], [silver_bid], [palladium_bid] etc...</strong>. Horray! The price will be output! This works in pages, posts and widgets. If you have any questions or require assistance email <a href="mailto:info@gold-feed.com">info@gold-feed.com</a>.
Version: 6.00
Author: Gold Feed Inc.
Author URI: https://gold-feed.com
*/

/*
	Copyright 2015 Gold Feed Inc.

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
	?><style type="text/css">
<!--
.style1 {color: #C00}
.style2 {color: #000099}
.style3 {
	color: #000000;
	font-style: italic;
	font-weight: bold;
}
.style4 {font-weight: bold}
.style5 {color: #FF0000}
.style6 {color: #CC9900}
.style7 {
	color: #006600;
	font-weight: bold;
}
.style8 {color: #000000}
-->
</style>
    <hr/>
    <h1 align="center" class="style1 style4"><span>THANK YOU FOR INSTALLING </span></h1>
<h1 align="center" class="style1"><span  style="color: #C00">(FREE) Gold Price Live!</span></h1>
<p align="center" class="style2">Brought to you by www.gold-feed.com </p>
<p align="center">Our FREE plugin updates precious metals pricing <u><strong>1 time per day</strong></u>. </p>
<p align="center" class="style3">We also have a paid version which updates <span class="style5">EVERY MINUTE!</span> See bottom of this page for more info and to subscribe.</p>
<p align="center" class="style3 style6">Our paid version also includes your logo on the charts!</p>
<h2 align="center"><u>USAGE INSTRUCTIONS</u>  </h2>
<h2 align="center">To add the value of Gold, Silver, Platinum and Palladium just write: </h2>
<div align="center" style=" font-weight:bold; font-size:9px;">[gold_chart]. [silver_chart], [gold_bid], [gold_ask], [gold_high], [gold_low], [gold_dollar_change], [gold_percent_change],<br>
   [silver_bid], [silver_ask], [silver_high], [silver_low], [silver_dollar_change], [silver_percent_change],<br>
    [platinum_bid], [platinum_ask], [platinum_high], [platinum_low], [platinum_dollar_change], [platinum_percent_change],<br>
     [palladium_bid], [palladium_ask], [palladium_high], [palladium_low], [palladium_dollar_change] or [palladium_percent_change]</pre></div>
<h4 align="center">In any of your Wordpress pages, posts or widgets.</h4>
<hr>
<h3 align="center">OUR PAID VERSION FEATURES </h3>
<p align="center">** Real Time Pricing Updated Every Minute ** </p>
<center><div style="width:325px; ">
  <p>Bid, Ask, High, Low, Dollar Change, Percent Change, 1st London Fix, 2nd London Fix</p>
</div>
<div style="background-color:#FFFFFF; border:1px solid #ff0000; width:70%;border-top-left-radius: 20px;border-bottom-right-radius: 20px;border-bottom-left-radius: 20px;border-top-right-radius: 20px;"><script type="text/javascript" src="https://secure.jotformpro.com/jsform/50804823354958"></script></div>
<div align="center" style="margin-top:15px; margin-bottom:15px;">&copy; 2014 Gold Feed Inc. | <a href="http://gold-feed.com" target="_blank">www.gold-feed.com</a> | <a href="mailto:info@gold-feed.com" style="color:#000000;">info@gold-feed.com</a> | <a href="tel:7804780870" style="color:#000000;">780-478-0870</a></div>
</center>

<hr>

    <?php

}


function oscimp_admin_actions() {
	

    //add_options_page("OSCommerce Product Display", "OSCommerce Product Display", 1, "OSCommerce Product Display", "oscimp_admin");

add_options_page('Gold Price Live', 'Gold_Price_Live', 'manage_options', 'Gold_Price_Live', 'oscimp_admin'); 

	}



add_action('admin_menu', 'oscimp_admin_actions');

//--------START GOLD CHART------------

//-------------------
function get_gold_chart($atts) {
$a=file_get_contents('http://gold-feed.com/charts/plugin3823832/goldchart.php');

	return $a;
}
 add_shortcode('gold_chart', 'get_gold_chart');
//-------------------

//--------START GOLD CHART------------

//-------------------
function get_silver_chart($atts) {
$a=file_get_contents('http://gold-feed.com/charts/plugin3823832/silverchart.php');

	return $a;
}
 add_shortcode('silver_chart', 'get_silver_chart');
//-------------------

//--------------------------------------------------------------------------------START GOLD

//--------START GOLD BID------------

//-------------------
function get_gold_bid($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/gold_bid.php');

	return $a;
}
 add_shortcode('gold_bid', 'get_gold_bid');
//-------------------

//--------START GOLD ASK------------

//-------------------
function get_gold_ask($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/gold_ask.php');

	return $a;
}
 add_shortcode('gold_ask', 'get_gold_ask');
//-------------------

//--------START GOLD HIGH------------

//-------------------
function get_gold_high($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/gold_high.php');

	return $a;
}
 add_shortcode('gold_high', 'get_gold_high');
//-------------------

//--------START GOLD LOW------------

//-------------------
function get_gold_low($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/gold_low.php');

	return $a;
}
 add_shortcode('gold_low', 'get_gold_low');
//-------------------

//--------START GOLD DOLLAR CHANGE------------

//-------------------
function get_gold_dollar_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/gold_dollar_change.php');

	return $a;
}
 add_shortcode('gold_dollar_change', 'get_gold_dollar_change');
//-------------------

//--------START GOLD PERCENT CHANGE------------

//-------------------
function get_gold_percent_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/gold_percent_change.php');

	return $a;
}
 add_shortcode('gold_percent_change', 'get_gold_percent_change');
//-------------------
//--------------------------------------------------------------------------------END GOLD

//--------------------------------------------------------------------------------START silver

//--------START silver BID------------

//-------------------
function get_silver_bid($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/silver_bid.php');

	return $a;
}
 add_shortcode('silver_bid', 'get_silver_bid');
//-------------------

//--------START silver ASK------------

//-------------------
function get_silver_ask($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/silver_ask.php');

	return $a;
}
 add_shortcode('silver_ask', 'get_silver_ask');
//-------------------

//--------START silver HIGH------------

//-------------------
function get_silver_high($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/silver_high.php');

	return $a;
}
 add_shortcode('silver_high', 'get_silver_high');
//-------------------

//--------START silver LOW------------

//-------------------
function get_silver_low($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/silver_low.php');

	return $a;
}
 add_shortcode('silver_low', 'get_silver_low');
//-------------------

//--------START silver DOLLAR CHANGE------------

//-------------------
function get_silver_dollar_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/silver_dollar_change.php');

	return $a;
}
 add_shortcode('silver_dollar_change', 'get_silver_dollar_change');
//-------------------

//--------START silver PERCENT CHANGE------------

//-------------------
function get_silver_percent_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/silver_percent_change.php');

	return $a;
}
 add_shortcode('silver_percent_change', 'get_silver_percent_change');
//-------------------
//--------------------------------------------------------------------------------END silver

//--------------------------------------------------------------------------------START platinum

//--------START platinum BID------------

//-------------------
function get_platinum_bid($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/platinum_bid.php');

	return $a;
}
 add_shortcode('platinum_bid', 'get_platinum_bid');
//-------------------

//--------START platinum ASK------------

//-------------------
function get_platinum_ask($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/platinum_ask.php');

	return $a;
}
 add_shortcode('platinum_ask', 'get_platinum_ask');
//-------------------

//--------START platinum HIGH------------

//-------------------
function get_platinum_high($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/platinum_high.php');

	return $a;
}
 add_shortcode('platinum_high', 'get_platinum_high');
//-------------------

//--------START platinum LOW------------

//-------------------
function get_platinum_low($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/platinum_low.php');

	return $a;
}
 add_shortcode('platinum_low', 'get_platinum_low');
//-------------------

//--------START platinum DOLLAR CHANGE------------

//-------------------
function get_platinum_dollar_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/platinum_dollar_change.php');

	return $a;
}
 add_shortcode('platinum_dollar_change', 'get_platinum_dollar_change');
//-------------------

//--------START platinum PERCENT CHANGE------------

//-------------------
function get_platinum_percent_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/platinum_percent_change.php');

	return $a;
}
 add_shortcode('platinum_percent_change', 'get_platinum_percent_change');
//-------------------
//--------------------------------------------------------------------------------END platinum

//--------------------------------------------------------------------------------START palladium

//--------START palladium BID------------

//-------------------
function get_palladium_bid($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/palladium_bid.php');

	return $a;
}
 add_shortcode('palladium_bid', 'get_palladium_bid');
//-------------------

//--------START palladium ASK------------

//-------------------
function get_palladium_ask($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/palladium_ask.php');

	return $a;
}
 add_shortcode('palladium_ask', 'get_palladium_ask');
//-------------------

//--------START palladium HIGH------------

//-------------------
function get_palladium_high($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/palladium_high.php');

	return $a;
}
 add_shortcode('palladium_high', 'get_palladium_high');
//-------------------

//--------START palladium LOW------------

//-------------------
function get_palladium_low($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/palladium_low.php');

	return $a;
}
 add_shortcode('palladium_low', 'get_palladium_low');
//-------------------

//--------START palladium DOLLAR CHANGE------------

//-------------------
function get_palladium_dollar_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/palladium_dollar_change.php');

	return $a;
}
 add_shortcode('palladium_dollar_change', 'get_palladium_dollar_change');
//-------------------

//--------START palladium PERCENT CHANGE------------

//-------------------
function get_palladium_percent_change($atts) {
$a=file_get_contents('http://vanseo.ca/freeplugin/palladium_percent_change.php');

	return $a;
}
 add_shortcode('palladium_percent_change', 'get_palladium_percent_change');
//-------------------
//--------------------------------------------------------------------------------END palladium



add_filter('widget_text', 'do_shortcode');

?>
