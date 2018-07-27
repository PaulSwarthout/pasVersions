<?php
   /*
   Plugin Name: Web Environment Version Information
   Plugin URI: http://www.paulswarthout.com/index.php/wordpress/
   Description: A plugin to display information about the Wordpress Web environment. This can be useful for both development and production. The output from this plugin appears on the Wordpress Dashboard only.
   Version: 1.2
   Author: Paul A. Swarthout
   License: GPL2
   */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once(dirname(__FILE__) . '/lib/plugin_constants.php');
require_once(dirname(__FILE__) . '/classes/class-webEnvironment.php');
require_once(dirname(__FILE__) . '/classes/class-serverInfo.php');

register_deactivation_hook(__FILE__, 'pasVersions_deactivate' );

add_action('admin_menu', 'show_php_version_menu' );
add_action('admin_enqueue_scripts', 'pasVersions_script' );
add_action('admin_enqueue_scripts', 'pasVersions_style' );
add_action('wp_ajax_hideMenuOption', 'hideMenuOption');
add_action('wp_ajax_pas_version_reveal_menu', 'revealMenuOption');
add_action('wp_dashboard_setup', 'pasVersions_dashboardWidgets');

function getConstant($c) {
	if (defined($c)) {
		return constant($c);
	} else {
		return false;
	}
}

$defaultOptions = PASVERSIONS_DEFAULT_OPTIONS;
$debugging = getConstant('WP_DEBUG', false);
//$plugin_directory = $pluginFolder;
$options =
	parseOptions(
		[ 'optionString' => get_option("pasVersionsOptions", $defaultOptions),
			'primaryDelimiter' => '/;/',
			'secondaryDelimiter' => '/:/'
		] );
$bInitComplete = ($options['INITCOMPLETE'] === "YES" ? true : false);
$bDashboard = ($options['DASHBOARD'] === "YES" ? true : false);
$bShowSensitive = ($options['SHOWSENSITIVE'] === "YES" ? true : false);
$bHideMenu = ($options['HIDEMENU'] === "YES" ? true : false);

function parseOptions($args) {
	$inputString				= $args['optionString'];
	$primaryDelimiter		= $args['primaryDelimiter'];
	$secondaryDelimiter = $args['secondaryDelimiter'];

	$options = Array();
	$primary = preg_split($primaryDelimiter, $inputString);
	for ($ndx = 0; $ndx < count($primary); $ndx++) {
		if (strlen($primary[$ndx]) > 0) {
			$secondary = preg_split($secondaryDelimiter, $primary[$ndx]);

			if (count($secondary) > 0) {
				$options[$secondary[0]] = $secondary[1];
			}
		}
	}
	return $options;
}

function pasVersions_script() {
	$pluginDirectory = plugin_dir_url(__FILE__);
	wp_enqueue_script( 'pasVersions_scripts', $pluginDirectory . 'js/pasVersions.js' . (getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
}
function pasVersions_style() {
	$pluginDirectory = plugin_dir_url(__FILE__);
	wp_enqueue_style( 'pasVersions_styles', $pluginDirectory . 'css/style.css' . (getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
}

function show_php_version_menu() {
	if (get_option('pasVersions_hideMenu', false) == false) {
		add_menu_page( 'VERSIONS', 'VERSIONS INFO', 'manage_options', 'pasVersionInfo', 'pasVersionInfo');
	}
}

function hideMenuOption() {
	update_option('pasVersions_hideMenu', true);
	wp_redirect(admin_url("index.php"));
	exit;
}
function revealMenuOption() {
	delete_option('pasVersions_hideMenu');
	exit;
}

function pasVersions_deactivate() {
	delete_option('pasVersions_hideMenu');
}

function pasVersionInfo() {
	echo "<div style='width:50%;border:solid 1pt #990000;padding:10px;'>";
	echo "<h2>Web Environment Version Information</h2>";
	echo "Thank you for installing the Web Environment Version Information plugin. This plugin displays the version information for your web environment as a dashboard widget.";
	echo "If you do not see it, please go to the 'Screen Options' pull down tab at the top of your Wordpress Dashboard and click to open the Screen Options window.";
	echo "Once there, you will see a check box for 'Web Environment'. Click to check that box and the Web Environment dashboard widget will display on your dashboard.";
	echo "<br><br>";
	echo "Now that you know where to find the information, if you think that you'll remember that, you can check the box below to hide this menu option (I know how cluttered the dashboard menu can get).<br>";

	echo "<br><input type='checkbox' value='" . get_option('pasVersions_hideMenu', 'HIDE MENU') . "' name='hide_menu' onclick='javascript:hideMenu(this);'> Hide VERSIONS INFO Menu<br>";
	echo "</div>";
}

function pasVersions_dashboardWidgets() {
	global $wp_meta_boxes;
	 
	wp_add_dashboard_widget('pasVersions_widget', 'Web Environment', 'pasVersions_dashboard');
}
function pasVersions_dashboard() {
	global $wp_version;
	global $wpdb;

	$webEnvironment = new Web_Environment("WHY IS THIS HERE", "WebEnvironment");
	$webEnvironment->initializeEnvironmentData();
	$webEnvironment->dumpEnvironmentData();
	unset($webEnvironment);
}
