<?php
   /*
   Plugin Name: Development Environment for WordPress
   Plugin URI: http://www.paulswarthout.com/wordpress/
   Description: A WordPress Dashboard widget, which displays information about the WordPress development environment. Since the information is displayed on the Dashboard, display items are limited by user capability. Sensitive items aren't shown to regular users.
   Version: 1.0
   Author: Paul A. Swarthout
   License: GPL2
   */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//require_once(dirname(__FILE__) . '/lib/plugin_constants.php');
require_once(dirname(__FILE__) . '/classes/class-webEnvironment.php');
require_once(dirname(__FILE__) . '/classes/class-serverInfo.php');

register_deactivation_hook(__FILE__, 'pasVersions_deactivate' );

add_action('admin_enqueue_scripts', 'pasVersions_script' );
add_action('admin_enqueue_scripts', 'pasVersions_style' );
add_action('wp_dashboard_setup', 'pasVersions_dashboardWidgets');

function getConstant($c) {
	if (defined($c)) {
		return constant($c);
	} else {
		return false;
	}
}

$debugging = getConstant('WP_DEBUG', false);

function pasVersions_script() {
	$pluginDirectory = plugin_dir_url(__FILE__);
	wp_enqueue_script( 'pasVersions_scripts', $pluginDirectory . 'js/pasVersions.js' . (getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
}
function pasVersions_style() {
	$pluginDirectory = plugin_dir_url(__FILE__);
	wp_enqueue_style( 'pasVersions_styles', $pluginDirectory . 'css/style.css' . (getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
}

function pasVersions_deactivate() {
	delete_option('pasVersions_firstLoad');
}

function pasVersionInfo() {
	echo "<div style='width:50%;border:solid 1pt #990000;padding:10px;'>";
	echo "<h2>Development Environment</h2>";
	echo "Thank you for installing the Development Environment plugin for WordPress. ";
	echo "This plugin displays the important information about your development environment. ";
	echo "If you do not see it, please go to the 'Screen Options' pull down tab at the top of your Wordpress Dashboard and click to open the Screen Options window.";
	echo "Once there, you will see a check box for 'Development Environment'. Click to check that box and the Development Environment dashboard widget will display on your dashboard.";
	echo "<br><br>";
	echo "Now that you know where to find the information, if you think that you'll remember that, you can check the box below to hide this menu option (I know how cluttered the dashboard menu can get).<br>";

	echo "<br><input type='checkbox' value='" . get_option('pasVersions_hideMenu', 'HIDE MENU') . "' name='hide_menu' onclick='javascript:hideMenu(this);'> Hide 'Dev Environment' Menu<br>";
	echo "</div>";

}

function pasVersions_dashboardWidgets() {
	global $wp_meta_boxes;
	 
	wp_add_dashboard_widget('pasVersions_widget', 'Development Environment', 'pasVersions_dashboard');
}
function pasVersions_dashboard() {
	global $wp_version;
	global $wpdb;

	$pluginDir = plugin_dir_url(__FILE__);

	$devEnvironment = new Dev_Environment($pluginDir, "DevEnvironment");
	$devEnvironment->initializeEnvironmentData();
	$devEnvironment->dumpEnvironmentData();
	unset($devEnvironment);
}
