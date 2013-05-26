<?php
/*
Plugin Name: Gold Feed Live
Plugin URI: http://wordpress.org/extend/plugins/gold-price-live
Description: Easily place the spot gold, silver, platinum and/or palladium price on your blog or website using shortcode. The price is per troy ounce and is in USD and is available in all currencies. Price output is never more than $0.07 different than the Kitco bid price of gold, silver, platinum and palladium. <strong>All you need to do is install the plugin to your Wordpress, activate the plugin and then use the shortcode [gold], [silver], [platinum] or [palladium]</strong>. Horray! The price will be output! This works in pages, posts and widgets. If you have any questions or require assistance email <a href="mailto:info@gold-feed.com">info@gold-feed.com</a>.
Version: 3.0
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

function get_gold($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/gold-subscribe.php');

	return $a;
}
 add_shortcode('gold', 'get_gold');

//----------------------------------------

function get_goldfeeder($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/gold-subscribe.php');

	return $a;
}
 add_shortcode('gold_feeder', 'get_gold');

//----------------------------------------

function get_silver($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/silver-subscribe.php');
	return $a;
}
 add_shortcode('silver', 'get_silver');
 
//-----------------------------------------------------

function get_silverfeeder($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/silver-subscribe.php');
	return $a;
}
 add_shortcode('silver_feeder', 'get_silver');
 
//-----------------------------------------------------

function get_platinum ($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/platinum-subscribe.php');

	return $a;
}
 add_shortcode('platinum', 'get_platinum ');

//-------------------------------

function get_palladium($atts) {
$a=file_get_contents('http://gold-feed.com/iframe/palladium-subscribe.php');

	return $a;
}
 add_shortcode('palladium', 'get_palladium');
add_filter('widget_text', 'do_shortcode');

?>
