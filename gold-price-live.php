<?php
/*
Plugin Name: Gold Feed Live
Plugin URI: http://wordpress.org/extend/plugins/gold-price-live
Description: Easily place the spot gold price on your blog or website using shortcode. The price is per troy ounce and is in USD. Price output is never more than $0.07 different than the Kitco bid price of gold. <strong>After activating please visit the Tools > Gold Price Live</strong> update options and enable. If you have any questions or require assistance email <a href="mailto:info@gold-feed.com">info@gold-feed.com</a>.
Version: 2.0
Author: Gold Feed Inc.
Author URI: https://gold-feed.com
*/

/*
	Copyright 2012 Gold Feed Inc.

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

/*
	Acknowledgments
	
*/

#error_reporting(E_ALL);

// Include support class
require_once('gold-price-live-class.php');

// Check pre-requisites
WPShortcodeExecPHP::Check_prerequisites();

// Start plugin
global $wp_shortcode_exec_php;
$wp_shortcode_exec_php = new WPShortcodeExecPHP();

// That's it!

?>
