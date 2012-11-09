<?php

/*
	Support class Gold Price Live
	Copyright (c) 2012 by Gold Feed Inc.
*/

if (!function_exists('is_plugin_active_for_network'))
	require_once(ABSPATH . '/wp-admin/includes/plugin.php');

// Define constants
define('c_scep_option_global', 'scep_global');
define('c_scep_option_widget', 'scep_widget');
define('c_scep_option_excerpt', 'scep_excerpt');
define('c_scep_option_comment', 'scep_comment');
define('c_scep_option_rss', 'scep_rss');
define('c_scep_option_noautop', 'scep_noautop');
define('c_scep_option_cleanup', 'scep_cleanup');
define('c_scep_option_donated', 'scep_donated');
define('c_scep_option_codewidth', 'scep_codewidth');
define('c_scep_option_codeheight', 'scep_codeheight');
define('c_scep_option_backtrack_limit', 'scep_backtrack_limit');
define('c_scep_option_recursion_limit', 'scep_recursion_limit');
define('c_scep_option_editarea_later', 'scep_editarea_later');
define('c_scep_option_tinymce', 'scep_tinymce');
define('c_scep_option_tinymce_cap', 'scep_tinymce_cap');
define('c_scep_option_author_cap', 'scep_author_cap');
define('c_scep_option_procode', 'scep_procode');

define('c_scep_option_names', 'scep_names');
define('c_scep_option_deleted', 'scep_deleted');
define('c_scep_option_enabled', 'scep_enabled_');
define('c_scep_option_buffer', 'scep_buffer_');
define('c_scep_option_description', 'scep_description_');
define('c_scep_option_param', 'scep_param_');
define('c_scep_option_phpcode', 'scep_phpcode_');

define('c_scep_form_enabled', 'scep_enabled');
define('c_scep_form_buffer', 'scep_buffer');
define('c_scep_form_shortcode', 'scep_shortcode');
define('c_scep_form_description', 'scep_description');
define('c_scep_form_phpcode', 'scep_phpcode');
define('c_scep_form_entry', 'scep_entry');

define('c_scep_nonce_form', 'scep-nonce-form');
define('c_scep_text_domain', 'shortcode-exec-php');

define('c_scep_action_arg', 'scep_action');
define('c_scep_param_nonce', 'nonce');
define('c_scep_param_name', 'name');
define('c_scep_param_shortcode', 'shortcode');
define('c_scep_param_enabled', 'enabled');
define('c_scep_param_buffer', 'buffer');
define('c_scep_param_description', 'description');
define('c_scep_param_phpcode', 'phpcode');
define('c_scep_action_save', 'save');
define('c_scep_action_test', 'test');
define('c_scep_action_delete', 'delete');
define('c_scep_action_new', 'new');
define('c_scep_action_revert', 'revert');
define('c_scep_action_tinymce', 'tinymce');
define('c_scep_action_export', 'export');

define('c_scep_nonce_ajax', 'scep-nonce-ajax');

// Extend SimpleXMLElement with CData
// http://coffeerings.posterous.com/php-simplexml-and-cdata
if (class_exists('SimpleXMLElement')) {
	class SimpleXMLExtended extends SimpleXMLElement {
		function addCData($cdata_text) {
			$node = dom_import_simplexml($this);
			$n = $node->ownerDocument;
			$node->appendChild($n->createCDATASection($cdata_text));
		}
	}
}

// Define class
if (!class_exists('WPShortcodeExecPHP')) {
	class WPShortcodeExecPHP {
		// Class variables
		var $main_file = null;
		var $plugin_url = null;
		var $default_backtrack_limit = null;
		var $default_recursion_limit = null;
		var $imported = 0;

		// Constructor
		function WPShortcodeExecPHP() {
			global $wp_version;

			$this->main_file = str_replace('-class', '', __FILE__);

			$this->plugin_url = WP_PLUGIN_URL . '/' . basename(dirname($this->main_file));
			if (strpos($this->plugin_url, 'http') === 0 && is_ssl())
				$this->plugin_url = str_replace('http://', 'https://', $this->plugin_url);

			// Register (de)activation hook
			register_activation_hook($this->main_file, array(&$this, 'Activate'));
			register_deactivation_hook($this->main_file, array(&$this, 'Deactivate'));

			// Register actions
			add_action('init', array(&$this, 'Init'), 0);
			if (is_admin()) {
				if (WPShortcodeExecPHP::Is_multisite() &&
					is_plugin_active_for_network(plugin_basename($this->main_file)) &&
					version_compare($wp_version, '3.1') >= 0)
					add_action('network_admin_menu', array(&$this, 'Admin_menu_network'));
				else
					add_action('admin_menu', array(&$this, 'Admin_menu'));
				add_action('wp_ajax_scep_ajax', array(&$this, 'Check_ajax'));
			}

			// Enable shortcodes for widgets
			if (WPShortcodeExecPHP::Get_option(c_scep_option_widget))
				add_filter('widget_text', 'do_shortcode');

			// Enable shortcodes for excerpts
			if (WPShortcodeExecPHP::Get_option(c_scep_option_excerpt))
			{
				add_filter('the_excerpt', 'do_shortcode');
				if (WPShortcodeExecPHP::Get_option(c_scep_option_comment))
					add_filter('comment_excerpt', 'do_shortcode');
			}

			// Enable shortcodes for comments
			if (WPShortcodeExecPHP::Get_option(c_scep_option_comment))
				add_filter('comment_text', 'do_shortcode');

			// Enable shortcodes for RSS
			if (WPShortcodeExecPHP::Get_option(c_scep_option_rss)) {
				if (version_compare($wp_version, '2.9') < 0)
					add_filter('the_content_rss', 'do_shortcode');
				else
					add_filter('the_content_feed',  'do_shortcode');
				if (WPShortcodeExecPHP::Get_option(c_scep_option_excerpt))
					add_filter('the_excerpt_rss', 'do_shortcode');
				if (WPShortcodeExecPHP::Get_option(c_scep_option_comment))
					add_filter('comment_text_rss', 'do_shortcode');
			}

			// wpautop handling
			if (WPShortcodeExecPHP::Get_option(c_scep_option_noautop)) {
				add_filter('the_content', array(&$this, 'noautop'), 1);
				add_filter('the_excerpt', array(&$this, 'noautop'), 1);
			}

			// Wire shortcode handlers
			$name = WPShortcodeExecPHP::Get_option(c_scep_option_names);
			for ($i = 0; $i < count($name); $i++)
				if (WPShortcodeExecPHP::Get_option(c_scep_option_enabled . $name[$i]))
					add_shortcode($name[$i], array(&$this, 'Shortcode_handler'));

			$this->default_backtrack_limit = ini_get('pcre.backtrack_limit');
			$this->default_recursion_limit = ini_get('pcre.recursion_limit');
			$this->Configure_prce();
		}

		function Configure_prce() {
			$backtrack_limit = WPShortcodeExecPHP::Get_option(c_scep_option_backtrack_limit);
			if ($backtrack_limit < $this->default_backtrack_limit)
				$backtrack_limit = $this->default_backtrack_limit;
			@ini_set('pcre.backtrack_limit', $backtrack_limit);

			$recursion_limit = WPShortcodeExecPHP::Get_option(c_scep_option_recursion_limit);
			if ($recursion_limit < $this->default_recursion_limit)
				$recursion_limit = $this->default_recursion_limit;
			@ini_set('pcre.recursion_limit', $recursion_limit);
		}

		function Init() {
			if (is_admin()) {
				// I18n
				load_plugin_textdomain(c_scep_text_domain, false, dirname(plugin_basename(__FILE__)));

				// Enqueue scripts
				wp_enqueue_script('jquery');
				wp_enqueue_script('simplemodal', $this->plugin_url . '/simplemodal/js/jquery.simplemodal.js');
				$procode = WPShortcodeExecPHP::Get_option(c_scep_option_procode);
				if (!empty($procode))
					wp_register_script('scepro', 'http://updates.faircode.eu/scepro?url=' . urlencode(self::Get_url()) . '&code=' .urlencode($procode));

				// Enqueue style sheet
				$css_name = $this->Change_extension(basename($this->main_file), '.css');
				if (file_exists(WP_CONTENT_DIR . '/uploads/' . $css_name))
					$css_url = WP_CONTENT_URL . '/uploads/' . $css_name;
				else if (file_exists(TEMPLATEPATH . '/' . $css_name))
					$css_url = get_bloginfo('template_directory') . '/' . $css_name;
				else
					$css_url = $this->plugin_url . '/' . $css_name;
				wp_register_style('scep_style', $css_url);
				wp_enqueue_style('scep_style');

				wp_register_style('simplemodal', $this->plugin_url . '/simplemodal/css/basic.css');
				wp_enqueue_style('simplemodal');

				// Make sure capabilities are set
				if (!WPShortcodeExecPHP::Get_option(c_scep_option_tinymce_cap))
					WPShortcodeExecPHP::Update_option(c_scep_option_tinymce_cap, 'edit_posts');
				if (!WPShortcodeExecPHP::Get_option(c_scep_option_author_cap))
					WPShortcodeExecPHP::Update_option(c_scep_option_author_cap, 'edit_posts');

				// http://codex.wordpress.org/TinyMCE_Custom_Buttons
				if (WPShortcodeExecPHP::Get_option(c_scep_option_tinymce) &&
					current_user_can(WPShortcodeExecPHP::Get_option(c_scep_option_tinymce_cap)) &&
					current_user_can(WPShortcodeExecPHP::Get_option(c_scep_option_author_cap)))
					if (current_user_can('edit_posts') || current_user_can('edit_pages'))
						if (get_user_option('rich_editing') == 'true') {
							add_filter('tiny_mce_version', array(&$this, 'TinyMCE_version') );
							add_filter('mce_external_plugins', array(&$this, 'TinyMCE_plugin'));
							add_filter('mce_buttons', array(&$this, 'TinyMCE_button'));
						}
			}
		}

		// Handle plugin activation
		function Activate() {
			if (!WPShortcodeExecPHP::Get_option(c_scep_option_codewidth))
				WPShortcodeExecPHP::Update_option(c_scep_option_codewidth,  600);
			if (!WPShortcodeExecPHP::Get_option(c_scep_option_codeheight))
				WPShortcodeExecPHP::Update_option(c_scep_option_codeheight, 200);

			if (!WPShortcodeExecPHP::Get_option(c_scep_option_names)) {
				// Define example shortcode
				$name = array();
				$name[] = 'hello_world';
				WPShortcodeExecPHP::Update_option(c_scep_option_names, $name);
				WPShortcodeExecPHP::Update_option(c_scep_option_enabled . $name[0], true);
				WPShortcodeExecPHP::Update_option(c_scep_option_buffer . $name[0], true);
				WPShortcodeExecPHP::Update_option(c_scep_option_description . $name[0], 'Example');
				$phpcode = "extract(shortcode_atts(array('arg' => 'default'), \$atts));" . PHP_EOL;
				$phpcode .= "echo \"Hello world!\" . PHP_EOL;" . PHP_EOL;
				$phpcode .= "echo \"Arg=\" . \$arg . PHP_EOL;" . PHP_EOL;
				$phpcode .= "echo \"Content=\" . \$content . PHP_EOL;" . PHP_EOL;
				WPShortcodeExecPHP::Update_option(c_scep_option_phpcode . $name[0], $phpcode);
			}

			// Fix spelling mistake
			if (WPShortcodeExecPHP::Get_option('scep_backtrace_limit')) {
				WPShortcodeExecPHP::Update_option(c_scep_option_backtrack_limit, WPShortcodeExecPHP::Get_option('scep_backtrace_limit'));
				WPShortcodeExecPHP::Delete_option('scep_backtrace_limit');
			}

			if (!WPShortcodeExecPHP::Get_option(c_scep_option_tinymce_cap))
				WPShortcodeExecPHP::Update_option(c_scep_option_tinymce_cap, 'edit_posts');
			if (!WPShortcodeExecPHP::Get_option(c_scep_option_author_cap))
				WPShortcodeExecPHP::Update_option(c_scep_option_author_cap, 'edit_posts');
		}

		// Handle plugin deactivation
		function Deactivate() {
			// Cleanup if requested
			if (WPShortcodeExecPHP::Get_option(c_scep_option_cleanup)) {
				delete_site_option(c_scep_option_global);
				WPShortcodeExecPHP::Delete_option(c_scep_option_widget);
				WPShortcodeExecPHP::Delete_option(c_scep_option_excerpt);
				WPShortcodeExecPHP::Delete_option(c_scep_option_comment);
				WPShortcodeExecPHP::Delete_option(c_scep_option_rss);
				WPShortcodeExecPHP::Delete_option(c_scep_option_noautop);
				WPShortcodeExecPHP::Delete_option(c_scep_option_codewidth);
				WPShortcodeExecPHP::Delete_option(c_scep_option_codeheight);
				WPShortcodeExecPHP::Delete_option(c_scep_option_editarea_later);
				WPShortcodeExecPHP::Delete_option(c_scep_option_backtrack_limit);
				WPShortcodeExecPHP::Delete_option(c_scep_option_recursion_limit);
				WPShortcodeExecPHP::Delete_option(c_scep_option_cleanup);
				WPShortcodeExecPHP::Delete_option(c_scep_option_donated);

				$name = WPShortcodeExecPHP::Get_option(c_scep_option_names);
				for ($i = 0; $i < count($name); $i++) {
					WPShortcodeExecPHP::Delete_option(c_scep_option_enabled . $name[$i]);
					WPShortcodeExecPHP::Delete_option(c_scep_option_buffer . $name[$i]);
					WPShortcodeExecPHP::Delete_option(c_scep_option_description . $name[$i]);
					WPShortcodeExecPHP::Delete_option(c_scep_option_param . $name[$i]);
					WPShortcodeExecPHP::Delete_option(c_scep_option_phpcode . $name[$i]);
				}
				WPShortcodeExecPHP::Delete_option(c_scep_option_names);
			}
		}

		// Admin head
		function Admin_head() {
			// Import shortcodes
			if (isset($_REQUEST['scep_action']) && $_REQUEST['scep_action'] == 'import')
				$this->imported = $this->Import();

			// Initialize EditArea
			$name = WPShortcodeExecPHP::Get_option(c_scep_option_names);
			$display = WPShortcodeExecPHP::Get_option(c_scep_option_editarea_later) ? 'later' : 'onload';

			echo '<script language="javascript" type="text/javascript">' . PHP_EOL;
			for ($i = 0; $i < count($name); $i++)
				echo 'editAreaLoader.init({id: "' . c_scep_form_phpcode . ($i + 1) . '", syntax: "php", start_highlight: true, display: "' . $display . '"});' . PHP_EOL;
			if ($display == 'onload')
				echo 'editAreaLoader.init({id: "' . c_scep_form_phpcode . '0", syntax: "php", start_highlight: true, EA_load_callback: "window.scrollTo(0,0);"});' . PHP_EOL;
			else
				echo 'editAreaLoader.init({id: "' . c_scep_form_phpcode . '0", syntax: "php", start_highlight: true, display: "later"});' . PHP_EOL;
			echo '</script>' . PHP_EOL;
		}

		// Register options page
		function Admin_menu() {
			if (WPShortcodeExecPHP::Is_multisite()) {
				if (function_exists('add_submenu_page'))
					$tools_page = add_submenu_page(
						'wpmu-admin.php',
						__('Gold Price Live Administration', c_scep_text_domain),
						__('Gold Price Live', c_scep_text_domain),
						is_plugin_active_for_network(plugin_basename($this->main_file)) ? 'manage_network' : 'manage_options',
						$this->main_file,
						array(&$this, 'Administration'));
			}
			else {
				if (function_exists('add_submenu_page'))
					$tools_page = add_submenu_page(
						'tools.php',
						__('Gold Price Live Administration', c_scep_text_domain),
						__('Gold Price Live', c_scep_text_domain),
						'manage_options',
						$this->main_file,
						array(&$this, 'Administration'));
			}

			// Hook admin head for option page
			if (!empty($tools_page)) {
				add_action('admin_head-' . $tools_page, array(&$this, 'Admin_head'));
				add_action('admin_print_styles-' . $tools_page, array(&$this, 'Print_scripts'));
			}
		}

		function Admin_menu_network() {
			if (function_exists('add_submenu_page'))
				$plugin_page = add_submenu_page(
					'settings.php',
					__('Gold Price Live Administration', c_scep_text_domain),
					__('Shortcode Exec PHP', c_scep_text_domain),
					'manage_network',
					$this->main_file,
					array(&$this, 'Administration'));

			// Hook admin head for option page
			if (!empty($plugin_page)) {
				add_action('admin_head-' . $plugin_page, array(&$this, 'Admin_head'));
				add_action('admin_print_styles-' . $plugin_page, array(&$this, 'Print_scripts'));
			}
		}

		function Print_scripts() {
			wp_enqueue_script('scepro');
		}

		// Handle option page
		function Administration() {
			// Secirity check
			if (!current_user_can(
				WPShortcodeExecPHP::Is_multisite() && is_plugin_active_for_network(plugin_basename($this->main_file))
				? 'manage_network' : 'manage_options'))
				die('Unauthorized');

			// Check post back
			if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
				// Check security
				check_admin_referer(c_scep_nonce_form);

				if (isset($_POST['scep_action']) && $_POST['scep_action'] == 'import')
					echo '<div id="message" class="updated fade"><p><strong>' .  __('Shortcodes imported:', c_scep_text_domain) . ' ' . $this->imported . '</strong></p></div>';
				else {
					if (empty($_POST[c_scep_option_global]))
						$_POST[c_scep_option_global] = null;
					if (empty($_POST[c_scep_option_widget]))
						$_POST[c_scep_option_widget] = null;
					if (empty($_POST[c_scep_option_excerpt]))
						$_POST[c_scep_option_excerpt] = null;
					if (empty($_POST[c_scep_option_comment]))
						$_POST[c_scep_option_comment] = null;
					if (empty($_POST[c_scep_option_rss]))
						$_POST[c_scep_option_rss] = null;
					if (empty($_POST[c_scep_option_noautop]))
						$_POST[c_scep_option_noautop] = null;
					if (empty($_POST[c_scep_option_editarea_later]))
						$_POST[c_scep_option_editarea_later] = null;
					if (empty($_POST[c_scep_option_tinymce]))
						$_POST[c_scep_option_tinymce] = null;
					if (empty($_POST[c_scep_option_cleanup]))
						$_POST[c_scep_option_cleanup] = null;
					if (empty($_POST[c_scep_option_donated]))
						$_POST[c_scep_option_donated] = null;

					// Update settings
					if (WPShortcodeExecPHP::Is_multisite() && function_exists('update_site_option'))
						update_site_option(c_scep_option_global, $_POST[c_scep_option_global]);
					WPShortcodeExecPHP::Update_option(c_scep_option_widget, $_POST[c_scep_option_widget]);
					WPShortcodeExecPHP::Update_option(c_scep_option_excerpt, $_POST[c_scep_option_excerpt]);
					WPShortcodeExecPHP::Update_option(c_scep_option_comment, $_POST[c_scep_option_comment]);
					WPShortcodeExecPHP::Update_option(c_scep_option_rss, $_POST[c_scep_option_rss]);
					WPShortcodeExecPHP::Update_option(c_scep_option_noautop, $_POST[c_scep_option_noautop]);
					WPShortcodeExecPHP::Update_option(c_scep_option_codewidth, trim($_POST[c_scep_option_codewidth]));
					WPShortcodeExecPHP::Update_option(c_scep_option_codeheight, trim($_POST[c_scep_option_codeheight]));
					WPShortcodeExecPHP::Update_option(c_scep_option_editarea_later, $_POST[c_scep_option_editarea_later]);
					WPShortcodeExecPHP::Update_option(c_scep_option_backtrack_limit, trim($_POST[c_scep_option_backtrack_limit]));
					WPShortcodeExecPHP::Update_option(c_scep_option_recursion_limit, trim($_POST[c_scep_option_recursion_limit]));
					WPShortcodeExecPHP::Update_option(c_scep_option_tinymce, $_POST[c_scep_option_tinymce]);
					WPShortcodeExecPHP::Update_option(c_scep_option_tinymce_cap, $_POST[c_scep_option_tinymce_cap]);
					WPShortcodeExecPHP::Update_option(c_scep_option_author_cap, $_POST[c_scep_option_author_cap]);
					WPShortcodeExecPHP::Update_option(c_scep_option_cleanup, $_POST[c_scep_option_cleanup]);
					WPShortcodeExecPHP::Update_option(c_scep_option_donated, $_POST[c_scep_option_donated]);

					$this->Configure_prce();

					// Copy options to site wide
					if ($_POST[c_scep_option_global] && WPShortcodeExecPHP::Is_multisite() && function_exists('update_site_option')) {
						$name = get_option(c_scep_option_names);
						update_site_option(c_scep_option_names, $name);
						for ($i = 0; $i < count($name); $i++) {
							update_site_option(c_scep_option_enabled . $name[$i], get_option(c_scep_option_enabled . $name[$i]));
							update_site_option(c_scep_option_buffer . $name[$i], get_option(c_scep_option_buffer . $name[$i]));
							update_site_option(c_scep_option_description . $name[$i], get_option(c_scep_option_description . $name[$i]));
							update_site_option(c_scep_option_param . $name[$i], get_option(c_scep_option_param . $name[$i]));
							update_site_option(c_scep_option_phpcode . $name[$i], get_option(c_scep_option_phpcode . $name[$i]));
						}
					}

					echo '<div id="message" class="updated fade"><p><strong>' . __('Settings updated', c_scep_text_domain) . '</strong></p></div>';
				}
			}

			if (isset($_REQUEST['procode'])) {
				$procode = $_REQUEST['procode'];
				WPShortcodeExecPHP::Update_option(c_scep_option_procode, $procode);
				WPShortcodeExecPHP::Update_option(c_scep_option_donated, !empty($procode));
				echo '<div id="message" class="updated fade"><p><strong>' . __('Code stored', c_scep_text_domain) . '</strong></p></div>';
			}

			echo '<div class="wrap">';

			// Get shortcodes
			$name = WPShortcodeExecPHP::Get_option(c_scep_option_names);
			WPShortcodeExecPHP::Update_option(c_scep_option_deleted, 0);
			usort($name, 'strcasecmp');

			// Render info panel
			$this->Render_info_panel();

			echo '<div id="scep_admin_panel">';

			// Render title
			echo '<h2>' . __('Gold Price Live Administration', c_scep_text_domain) . '</h2>';

			// Render options form
			echo '<form method="post" action="">';

			// Security
			wp_nonce_field(c_scep_nonce_form);
			$nonce = wp_create_nonce(c_scep_nonce_ajax);

			// Get current settings
			$scep_global = (WPShortcodeExecPHP::Is_multisite() && function_exists('get_site_option') && get_site_option(c_scep_option_global) ? 'checked="checked"' : '');
			$scep_widget = (WPShortcodeExecPHP::Get_option(c_scep_option_widget) ? 'checked="checked"' : '');
			$scep_excerpt = (WPShortcodeExecPHP::Get_option(c_scep_option_excerpt) ? 'checked="checked"' : '');
			$scep_comment = (WPShortcodeExecPHP::Get_option(c_scep_option_comment) ? 'checked="checked"' : '');
			$scep_rss = (WPShortcodeExecPHP::Get_option(c_scep_option_rss) ? 'checked="checked"' : '');
			$scep_noautop = (WPShortcodeExecPHP::Get_option(c_scep_option_noautop) ? 'checked="checked"' : '');
			$scep_width = (WPShortcodeExecPHP::Get_option(c_scep_option_codewidth));
			$scep_height = (WPShortcodeExecPHP::Get_option(c_scep_option_codeheight));
			$scep_editarea_later = (WPShortcodeExecPHP::Get_option(c_scep_option_editarea_later) ? 'checked="checked"' : '');
			$scep_backtrack_limit = (WPShortcodeExecPHP::Get_option(c_scep_option_backtrack_limit));
			$scep_recursion_limit = (WPShortcodeExecPHP::Get_option(c_scep_option_recursion_limit));
			$scep_option_tinymce = (WPShortcodeExecPHP::Get_option(c_scep_option_tinymce) ? 'checked="checked"' : '');
			$scep_option_tinymce_cap = WPShortcodeExecPHP::Get_option(c_scep_option_tinymce_cap);
			$scep_option_author_cap = WPShortcodeExecPHP::Get_option(c_scep_option_author_cap);
			$scep_cleanup = (WPShortcodeExecPHP::Get_option(c_scep_option_cleanup) ? 'checked="checked"' : '');
			$scep_donated = (WPShortcodeExecPHP::Get_option(c_scep_option_donated) ? 'checked="checked"' : '');

			// Default size
			if ($scep_width <= 0)
				$scep_width = 600;
			if ($scep_height <= 0)
				$scep_height = 200;

			// Get list of capabilities
			global $wp_roles;
			$capabilities = array();
			foreach ($wp_roles->role_objects as $key => $role)
				if (is_array($role->capabilities))
					foreach ($role->capabilities as $cap => $grant)
						$capabilities[$cap] = $cap;
			sort($capabilities);

			// Render options
?>
			<h3><?php _e('Options', c_scep_text_domain); ?></h3>
			<table id="scep_option_table" class="form-table">
<?php		if (WPShortcodeExecPHP::Is_multisite() && function_exists('update_site_option')) { ?>
				<tr valign="top"><th scope="row">
					<label for="scep_option_global"><?php _e('Make shortcodes global', c_scep_text_domain); ?></label>
				</th><td>
					<input name="<?php echo c_scep_option_global; ?>" type="checkbox" id="scep_option_global" checked checked<?php echo $scep_global; ?> />
				</td></tr>
<?php		} ?>

			<tr valign="top"><th scope="row">
				<label for="scep_option_widget"><?php _e('Allow <b>Gold Price Live</b> in (sidebar) widgets', c_scep_text_domain); ?></label>
			</th><td>
				<input name="<?php echo c_scep_option_widget; ?>" type="checkbox" id="scep_option_widget" checked checked<?php echo $scep_widget; ?> />
			</td></tr>

			<tr valign="top"><th scope="row">
				<label for="scep_option_excerpt"><?php _e('Allow <b>Gold Price Live</b> in excerpts', c_scep_text_domain); ?></label>
			</th><td>
				<input name="<?php echo c_scep_option_excerpt; ?>" type="checkbox" id="scep_option_excerpt" checked checked<?php echo $scep_excerpt; ?> />
			</td></tr>

			<tr valign="top"><th scope="row">
				<label for="scep_option_comment"><?php _e('Allow <b>Gold Price Live</b> in comments', c_scep_text_domain); ?></label>
			</th><td>
				<input name="<?php echo c_scep_option_comment; ?>" type="checkbox" id="scep_option_comment" checked checked<?php echo $scep_comment; ?> />
			</td></tr>

			<tr valign="top"><th scope="row">
				<label for="scep_option_rss"><?php _e('Allow <b>Gold Price Live</b> in RSS feeds', c_scep_text_domain); ?></label>
			</th><td>
				<input name="<?php echo c_scep_option_rss; ?>" type="checkbox" id="scep_option_rss" checked checked<?php echo $scep_rss; ?> />
			</td></tr>
			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update Options', c_scep_text_domain); ?>" />
			</p>

			</form>
			<h3>Gold Price Live Shortcode (<span style="color:#C00; font-weight:bold;">FREE VERSION</span>)</h3>
<?php
			// Render shortcode definitions
			for ($i = 0; $i < count($name); $i++) {
				$enabled = WPShortcodeExecPHP::Get_option(c_scep_option_enabled . $name[$i]);
				$buffer = WPShortcodeExecPHP::Get_option(c_scep_option_buffer . $name[$i]);
				$description = WPShortcodeExecPHP::Get_option(c_scep_option_description . $name[$i]);
				$code = WPShortcodeExecPHP::Get_option(c_scep_option_phpcode . $name[$i]);
				$this->Render_shortcode_form($name[$i], $i + 1, $description, $enabled, $buffer, $code);
			}
?>
			<table><tr><td>
			<form method="post" action="" id="scep-new">
			<table>
			<tr><td>[<input name="<?php echo c_scep_form_shortcode; ?>0" type="text" value="gold_feeder" readonly="readonly">]</td></tr>
			<tr>
			  <td><textarea name="<?php echo c_scep_form_phpcode; ?>0" disabled readonly="readonly" class="scep_table_code" id="<?php echo c_scep_form_phpcode; ?>0"
			style="width:<?php echo $scep_width; ?>px;height:<?php echo $scep_height; ?>px;">$goldfeed = file_get_contents('http://gold-feed.com/iframe/gold-feed-plain.php');
echo $goldfeed;</textarea></td></tr>
			<tr><td align="right">
			<span name="scep_message" class="scep_message"></span>
			<img src="<?php echo $this->plugin_url  . '/img/ajax-loader.gif'; ?>" alt="wait" name="scep_wait" style="display: none;" />
			<input type="submit" class="button-primary" value="<?php _e('Confirm and Enable', c_scep_text_domain); ?>" /></td></tr>
			</table>
			</form>
			</td>
			</tr>
			</table>
			<hr />

			</div>
			</div>

			<script type="text/javascript">
			/* <![CDATA[ */
			jQuery(document).ready(function($) {
				/* new shortcode */
				$('#scep-new').submit(function() {
					editid = '<?php echo c_scep_form_phpcode; ?>0';
					shortcode = $('[name=<?php echo c_scep_form_shortcode; ?>0]').val();
					phpcode = editAreaLoader.getValue(editid);
					display = '<?php echo WPShortcodeExecPHP::Get_option(c_scep_option_editarea_later) ? 'later' : 'onload'; ?>';
					msg = $('[name=scep_message]', this);
					wait = $('[name=scep_wait]', this);
					input = $('input,textarea', this);

					msg.text('');
					wait.show();
					input.attr('disabled', 'disabled');
					editAreaLoader.execCommand(editid, 'set_editable', false);

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'scep_ajax',
							<?php echo c_scep_param_nonce; ?>: '<?php echo $nonce; ?>',
							<?php echo c_scep_action_arg; ?>:  '<?php echo c_scep_action_new; ?>',
							<?php echo c_scep_param_shortcode; ?>: shortcode,
							<?php echo c_scep_param_phpcode; ?>: phpcode
						},
						dataType: 'text',
						cache: false,
						success: function(result) {
							wait.hide();
							input.removeAttr('disabled');
							editAreaLoader.execCommand(editid, 'set_editable', true);

							index = result.substring(0, result.indexOf('|'));
							html = result.substring(result.indexOf('|') + 1);
							if (index > 0) {
								$('#scep-new').before(html);
								editAreaLoader.init({id: '<?php echo c_scep_form_phpcode; ?>' + index, syntax: 'php', start_highlight: true, display: display});
								$('[name=<?php echo c_scep_form_shortcode; ?>0]').val('');
								editAreaLoader.setValue(editid, '');
							}
							else
								msg.text(html);
						},
						error: function(x, stat, e) {
							wait.hide();
							input.removeAttr('disabled');
							editAreaLoader.execCommand(editid, 'set_editable', true);
							msg.text('<?php _e('Error', c_scep_text_domain); ?>' + ' ' + x.status);
						}
					});
					return false;
				});

				/* test, save, revert, delete shortcode */
				$('.scep-update').live('click', function() {
					action = this.name;
					entryid = '<?php echo c_scep_form_entry; ?>' + this.form.id;
					orgname = this.form.name;
					editid = '<?php echo c_scep_form_phpcode; ?>' + this.form.id;f
					shortcode = $('[name=<?php echo c_scep_form_shortcode; ?>' + this.form.id + ']').val();
					description = $('[name=<?php echo c_scep_form_description; ?>' + this.form.id + ']').val();
					enabled = $('[name=<?php echo c_scep_form_enabled; ?>' + this.form.id + ']').attr('checked');
					buffer = $('[name=<?php echo c_scep_form_buffer; ?>' + this.form.id + ']').attr('checked');
					if (enabled == 'checked')
						enabled = $('[name=<?php echo c_scep_form_enabled; ?>' + this.form.id + ']').prop('checked');
					if (buffer == 'checked')
						buffer = $('[name=<?php echo c_scep_form_buffer; ?>' + this.form.id + ']').prop('checked');
					phpcode = editAreaLoader.getValue(editid);
					msg = $('[name=scep_message]', this.form);
					wait = $('[name=scep_wait]', this.form);
					input = $('input,textarea', this.form);

					if (action == '<?php echo c_scep_action_delete; ?>')
						if (!confirm('<?php _e('Are you sure to delete', c_scep_text_domain); ?> [' + orgname + ']?'))
							return false;

					input.attr('disabled', 'true');
					editAreaLoader.execCommand(editid, 'set_editable', false);
					msg.text('');
					wait.show();

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'scep_ajax',
							<?php echo c_scep_param_nonce; ?>: '<?php echo $nonce; ?>',
							<?php echo c_scep_action_arg; ?>:  action,
							<?php echo c_scep_param_name; ?>: orgname,
							<?php echo c_scep_param_shortcode; ?>: shortcode,
							<?php echo c_scep_param_description; ?>: description,
							<?php echo c_scep_param_phpcode; ?>: phpcode,
							<?php echo c_scep_param_enabled; ?>: enabled,
							<?php echo c_scep_param_buffer; ?>: buffer
						},
						dataType: 'text',
						cache: false,
						success: function(result) {
							wait.hide();
							input.removeAttr('disabled');
							editAreaLoader.execCommand(editid, 'set_editable', true);

							if (action == '<?php echo c_scep_action_test; ?>')
								if (typeof scepro == 'function')
									scepro($, result);
								else
									alert(result);
							else if (action == '<?php echo c_scep_action_revert; ?>')
								editAreaLoader.setValue(editid, result);
							else if (action == '<?php echo c_scep_action_delete; ?>')
								$('#' + entryid).remove();
							else
								msg.text(result);
						},
						error: function(x, stat, e) {
							wait.hide();
							input.removeAttr('disabled');
							editAreaLoader.execCommand(editid, 'set_editable', true);
							msg.text('<?php _e('Error', c_scep_text_domain); ?>' + ' ' + x.status);
						}
					});
					return false;
				});
			});
			/* ]]> */
			</script>
<?php
		}

		// Render shortcode edit form
		function Render_shortcode_form($name, $i, $description, $enabled, $buffer, $code) {
			$scep_width = WPShortcodeExecPHP::Get_option(c_scep_option_codewidth);
			$scep_height = WPShortcodeExecPHP::Get_option(c_scep_option_codeheight);

			if ($scep_width <= 0)
				$scep_width = 600;
			if ($scep_height <= 0)
				$scep_height = 200;
?>
			<a name="<?php echo $name; ?>"></a>
			
<?php
		}

		function Render_info_panel() {
?>
			<div id="scep_resources_panel">
			<h3><?php _e('Other Stuff We Offer', c_scep_text_domain); ?></h3>
			<ul>
			<li><a href="https://gold-feed.com/" target="_blank">Custom Gold Payout Charts</a></li>
			</ul>
			</div>
<?php
		}

		// Shortcode execution
		function Shortcode_handler($atts, $content, $code) {
			// Check if in RSS feed
			if (is_feed() && !WPShortcodeExecPHP::Get_option(c_scep_option_rss))
				return '[' . $code . ']' . ($content ? $content . '[/' . $code . ']' : '');

			// Security check
			global $post;
			global $in_comment_loop;
			if (!in_the_loop() ||
				!empty($in_comment_loop) ||
				author_can($post, WPShortcodeExecPHP::Get_option(c_scep_option_author_cap))) {
				// Log last used parameters
				if ($atts)
					WPShortcodeExecPHP::Update_option(c_scep_option_param . $code, $atts);
				else
					WPShortcodeExecPHP::Delete_option(c_scep_option_param . $code);

				$buffer = WPShortcodeExecPHP::Get_option(c_scep_option_buffer . $code);
				if ($buffer)
					ob_start();
				$result = eval(WPShortcodeExecPHP::Get_option(c_scep_option_phpcode . $code));
				if ($buffer) {
					$output = ob_get_contents();
					ob_end_clean();
				}
				else
					$output = '';

				return $output . $result;
			}
			else
				return '[' . $code . ']?';
		}

		// Handle ajax calls
		function Check_ajax() {
			if (isset($_REQUEST[c_scep_action_arg])) {

				// Handle TinyMCE
				if ($_REQUEST[c_scep_action_arg] == c_scep_action_tinymce) {
					$this->TinyMCE_handle();
					exit();
				}

				// Security check
				$nonce = $_REQUEST[c_scep_param_nonce];
				if (!wp_verify_nonce($nonce, c_scep_nonce_ajax))
					die('Unauthorized');

				// Handle export
				if ($_REQUEST[c_scep_action_arg] == c_scep_action_export) {
					$this->Export();
					exit();
				}

				// Send header
				header('Content-Type: text/html; charset=' . get_option('blog_charset'));

				// Load text domain
				load_plugin_textdomain(c_scep_text_domain, false, basename(dirname($this->main_file)));

				if (empty($_REQUEST[c_scep_param_name]))
					$_REQUEST[c_scep_param_name] = null;
				if (empty($_REQUEST[c_scep_param_shortcode]))
					$_REQUEST[c_scep_param_shortcode] = null;
				if (empty($_REQUEST[c_scep_param_description]))
					$_REQUEST[c_scep_param_description] = null;
				if (empty($_REQUEST[c_scep_param_phpcode]))
					$_REQUEST[c_scep_param_phpcode] = null;

				if (empty($_REQUEST[c_scep_param_enabled]))
					$_REQUEST[c_scep_param_enabled] = false;
				else if ($_REQUEST[c_scep_param_enabled] == 'true')
					$_REQUEST[c_scep_param_enabled] = true;
				else
					$_REQUEST[c_scep_param_enabled] = false;

				if (empty($_REQUEST[c_scep_param_buffer]))
					$_REQUEST[c_scep_param_buffer] = false;
				else if ($_REQUEST[c_scep_param_buffer] == 'true')
					$_REQUEST[c_scep_param_buffer] = true;
				else
					$_REQUEST[c_scep_param_buffer] = false;

				// Decode parameters
				$name = stripslashes($_REQUEST[c_scep_param_name]);
				$shortcode = trim(stripslashes($_REQUEST[c_scep_param_shortcode]));
				$enabled = $_REQUEST[c_scep_param_enabled];
				$buffer = $_REQUEST[c_scep_param_buffer];
				$description = trim(stripslashes($_REQUEST[c_scep_param_description]));
				$phpcode = $_REQUEST[c_scep_param_phpcode];
				$phpcode = stripslashes($phpcode);

				// Save, test
				if ($_POST[c_scep_action_arg] == c_scep_action_save || $_POST[c_scep_action_arg] == c_scep_action_test) {
					// Persist new definition
					$names = WPShortcodeExecPHP::Get_option(c_scep_option_names);
					for ($i = 0; $i < count($names); $i++)
						if ($names[$i] == $name) {
							remove_shortcode($name);
							WPShortcodeExecPHP::Delete_option(c_scep_option_enabled . $name);
							WPShortcodeExecPHP::Delete_option(c_scep_option_buffer . $name);
							WPShortcodeExecPHP::Delete_option(c_scep_option_description . $name);
							WPShortcodeExecPHP::Delete_option(c_scep_option_param . $name);
							WPShortcodeExecPHP::Delete_option(c_scep_option_phpcode . $name);
							$names[$i] = $shortcode;
							break;
						}
					WPShortcodeExecPHP::Update_option(c_scep_option_names, $names);
					WPShortcodeExecPHP::Update_option(c_scep_option_enabled . $shortcode, $enabled);
					WPShortcodeExecPHP::Update_option(c_scep_option_buffer . $shortcode, $buffer);
					WPShortcodeExecPHP::Update_option(c_scep_option_description . $shortcode, $description);
					WPShortcodeExecPHP::Update_option(c_scep_option_phpcode . $shortcode, $phpcode);

					if ($_POST[c_scep_action_arg] == c_scep_action_save)
						echo __('Saved', c_scep_text_domain);
					else
						// Test shortcode
						if ($enabled) {
							ob_start();
							$result = eval($phpcode);
							$output = ob_get_contents();
							ob_end_clean();

							if ($buffer) {
								$result = $output . $result;
								$output = false;
							}

							echo '[' . $shortcode . ']="' . $result . '"';
							if ($output) {
								echo PHP_EOL . __('Unexpected output, do not use ECHO but RETURN', c_scep_text_domain);
								echo PHP_EOL . '"' . $output . '"';
							}
						}
						else
							echo '[' . $shortcode . '] ' . __('not enabled', c_scep_text_domain);
				}

				// Revert
				else if ($_POST[c_scep_action_arg] == c_scep_action_revert)
					echo WPShortcodeExecPHP::Get_option(c_scep_option_phpcode . $name, $phpcode);

				// Delete
				else if ($_POST[c_scep_action_arg] == c_scep_action_delete) {
					$names = WPShortcodeExecPHP::Get_option(c_scep_option_names);
					for ($i = 0; $i < count($names); $i++)
						if ($names[$i] == $name) {
							remove_shortcode($name);
							array_splice($names, $i, 1);
							break;
						}
					WPShortcodeExecPHP::Update_option(c_scep_option_names, $names);
					WPShortcodeExecPHP::Delete_option(c_scep_option_enabled . $name);
					WPShortcodeExecPHP::Delete_option(c_scep_option_buffer . $name);
					WPShortcodeExecPHP::Delete_option(c_scep_option_description . $name);
					WPShortcodeExecPHP::Delete_option(c_scep_option_param . $name);
					WPShortcodeExecPHP::Delete_option(c_scep_option_phpcode . $name);
					WPShortcodeExecPHP::Update_option(c_scep_option_deleted, WPShortcodeExecPHP::Get_option(c_scep_option_deleted) + 1);
				}

				// New
				else if ($_POST[c_scep_action_arg] == c_scep_action_new) {
					// Check unique
					$names = WPShortcodeExecPHP::Get_option(c_scep_option_names);
					for ($i = 0; $i < count($names); $i++)
						if ($names[$i] == $shortcode) {
							echo '0|' . __('Shortcode exists', c_scep_text_domain);
							exit();
						}

					if ($shortcode) {
						$names = WPShortcodeExecPHP::Get_option(c_scep_option_names);
						$names[] = $shortcode;
						WPShortcodeExecPHP::Update_option(c_scep_option_names, $names);
						WPShortcodeExecPHP::Add_option(c_scep_option_enabled . $shortcode, true);
						WPShortcodeExecPHP::Add_option(c_scep_option_buffer . $shortcode, true);
						WPShortcodeExecPHP::Add_option(c_scep_option_description . $shortcode, '');
						WPShortcodeExecPHP::Add_option(c_scep_option_phpcode . $shortcode, $phpcode);
						$index = count($names) + WPShortcodeExecPHP::Get_option(c_scep_option_deleted);
						echo $index . '|';
						echo $this->Render_shortcode_form($shortcode, $index, '', true, true, $phpcode);
					}
					else {
						echo '0|' . __('Name missing', c_scep_text_domain);
						exit();
					}
				}

				// Otherwise
				else
					die('Unknown request');

				exit();
			}
		}

		// Export shortcodes
		function Export() {
			// Create root element
			$root = new SimpleXMLExtended('<shortcode-exec-php></shortcode-exec-php>');

			// Add each shortcode
			$name = WPShortcodeExecPHP::Get_option(c_scep_option_names);
			for ($i = 0; $i < count($name); $i++) {
				// Get attributes
				$enabled = WPShortcodeExecPHP::Get_option(c_scep_option_enabled . $name[$i]);
				$buffer = WPShortcodeExecPHP::Get_option(c_scep_option_buffer . $name[$i]);
				$description = WPShortcodeExecPHP::Get_option(c_scep_option_description . $name[$i]);
				$code = WPShortcodeExecPHP::Get_option(c_scep_option_phpcode . $name[$i]);

				// Create element for shortcode
				$element = $root->addChild('shortcode');
				$element->addAttribute('name', $name[$i]);
				$element->addAttribute('enabled', $enabled ? 'true' : 'false');
				$element->addAttribute('buffer', $buffer ? 'true' : 'false');
				$element->addAttribute('description', $description);
				$element->addCData($code);
			}

			// Output
			header('Content-type: text/xml');
			header('Content-Disposition: attachment; filename="shortcode-exec-php.xml"');
			echo $root->asXML();
		}

		// Import shortcodes
		function Import() {
			// Security check
			check_admin_referer(c_scep_nonce_form);

			// Check upload
			$count = 0;
			if (!empty($_FILES) && !empty($_FILES['scep_file']) && $_FILES['scep_file']['error'] == UPLOAD_ERR_OK) {
				// Decode file
				$xml = simplexml_load_file($_FILES['scep_file']['tmp_name'], NULL, LIBXML_NOCDATA);

				// Get names
				$names = WPShortcodeExecPHP::Get_option(c_scep_option_names);

				// Traverse shortcodes
				foreach ($xml->shortcode as $shortcode) {
					// Get attributes
					$attr = $shortcode->attributes();
					$name = (string)$attr['name'];

					// Delete existing shortcode name
					for ($i = 0; $i < count($names); $i++)
						if ($names[$i] == $name) {
							array_splice($names, $i, 1);
							break;
						}

					// Add/update shortcode
					$names[] = $name;
					WPShortcodeExecPHP::Update_option(c_scep_option_enabled . $name, $attr['enabled'] == 'true' ? true : false);
					WPShortcodeExecPHP::Update_option(c_scep_option_buffer . $name, $attr['buffer'] == 'true' ? true : false);
					WPShortcodeExecPHP::Update_option(c_scep_option_description . $name, (string)$attr['description']);
					WPShortcodeExecPHP::Update_option(c_scep_option_phpcode . $name, (string)$shortcode);

					$count++;
				}

				// Update names
				WPShortcodeExecPHP::Update_option(c_scep_option_names, $names);
			}
			return $count;
		}

		// Disable wpautop
		function noautop($content) {
			remove_filter('the_content', 'wpautop');
			remove_filter('the_excerpt', 'wpautop');
			return $content;
		}

		// TinyMCE integration

		function TinyMCE_version($version) {
			return $version . 'scep';
		}

		function TinyMCE_plugin($plugins) {
			$plugins['ShortcodeExecPHP'] =  $this->plugin_url . '/tinymce/shortcode.js';
			return $plugins;
		}

		function TinyMCE_button($buttons) {
			array_push($buttons, 'separator', 'ShortcodeExecPHP');
			return $buttons;
		}

		function TinyMCE_handle() {
			// Load text domain
			load_plugin_textdomain(c_scep_text_domain, false, basename(dirname($this->main_file)));

			$names = WPShortcodeExecPHP::Get_option(c_scep_option_names);

			// Send header
			header('Content-Type: text/html; charset=' . get_option('blog_charset'));
?>
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<title>Shortcode</title>
			<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
			<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
			<script type='text/javascript'>
				/* <![CDATA[ */
				jQuery(document).ready(function($) {
					$('#scep-tinymce-shortcode').change(function() {
						$('.scep-description').hide();
						$('#scep-description-' + $(this).val()).show();
					});

					$('#scep-tinymce-form').submit(function() {
						if (window.tinyMCE) {
							window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, '[' + $('#scep-tinymce-shortcode').val() + ']');
							tinyMCEPopup.editor.execCommand('mceRepaint');
							tinyMCEPopup.close();
						}
						return false;
					});
				});
				/* ]]> */
			</script>
			</head>
			<body>
			<form method="post" action="#" id="scep-tinymce-form">
			<select id="scep-tinymce-shortcode">
<?php
			if (WPShortcodeExecPHP::Get_option(c_scep_option_tinymce) &&
				current_user_can(WPShortcodeExecPHP::Get_option(c_scep_option_tinymce_cap)) &&
				current_user_can(WPShortcodeExecPHP::Get_option(c_scep_option_author_cap)))
				foreach ($names as $name)
					echo '<option value="' . $name . '">' . htmlspecialchars($name) . '</option>' . PHP_EOL;
?>
			</select>
			<br />
<?php
			if (WPShortcodeExecPHP::Get_option(c_scep_option_tinymce) &&
				current_user_can(WPShortcodeExecPHP::Get_option(c_scep_option_tinymce_cap)) &&
				current_user_can(WPShortcodeExecPHP::Get_option(c_scep_option_author_cap)))
				for ($i = 0; $i < count($names); $i++) {
					$description = WPShortcodeExecPHP::Get_option(c_scep_option_description . $names[$i]);
					$display = ($i ? ' style="display: none;"' : '');
					echo '<p class="scep-description" id="scep-description-' . $names[$i] . '"' . $display . '>' . htmlspecialchars($description) . '</p>';
				}
?>
			<br />
			<input type="submit" value="<?php _e('Insert', c_scep_text_domain); ?>" />
			</form>
			</body>
			</html>
<?php
		}

		// Helpers global option management

		function Add_option($name, $value) {
			if (WPShortcodeExecPHP::Is_multisite() && function_exists('add_site_option'))
				add_site_option($name, $value);
			add_option($name, $value);
		}

		function Update_option($name, $value) {
			if (WPShortcodeExecPHP::Is_multisite() && function_exists('update_site_option'))
				update_site_option($name, $value);
			update_option($name, $value);
		}

		function Delete_option($name) {
			if (WPShortcodeExecPHP::Is_multisite() && function_exists('delete_site_option'))
				delete_site_option($name);
			delete_option($name);
		}

		function Get_option($name) {
			if (WPShortcodeExecPHP::Is_multisite() && function_exists('get_site_option') && get_site_option(c_scep_option_global))
				return get_site_option($name);
			return get_option($name);
		}

		// Helper check if network site
		function Is_multisite() {
			global $wpmu_version;
			return (function_exists('is_multisite') && is_multisite()) || !empty($wpmu_version);
		}

		function Get_url() {
			if (is_multisite()) {
				$current_site = get_current_site();
				$blog_details = get_blog_details($current_site->blog_id, true);
				$main_site_url = strtolower(trailingslashit($blog_details->siteurl));
				return $main_site_url;
			}
			else
				return strtolower(trailingslashit(get_site_url()));
		}

		// Helper check environment
		function Check_prerequisites() {
			// Check PHP version
			if (version_compare(PHP_VERSION, '4.3.0', '<'))
				die('Shortcode Exec PHP requires at least PHP 4.3.0');

			// Check WordPress version
			global $wp_version;
			if (version_compare($wp_version, '2.8') < 0)
				die('Shortcode Exec PHP requires at least WordPress 2.8');

			// Check basic prerequisities
			WPShortcodeExecPHP::Check_function('register_activation_hook');
			WPShortcodeExecPHP::Check_function('register_deactivation_hook');
			WPShortcodeExecPHP::Check_function('add_action');
			WPShortcodeExecPHP::Check_function('add_filter');
			WPShortcodeExecPHP::Check_function('wp_enqueue_script');
			WPShortcodeExecPHP::Check_function('wp_register_style');
			WPShortcodeExecPHP::Check_function('wp_enqueue_style');
		}

		function Check_function($name) {
			if (!function_exists($name))
				die('Required WordPress function "' . $name . '" does not exist');
		}

		// Helper change file extension
		function Change_extension($filename, $new_extension) {
			return preg_replace('/\..+$/', $new_extension, $filename);
		}
	}
}

?>
