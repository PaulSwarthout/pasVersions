<?php
/*
   Plugin Name: Web Server Environment for WordPress
   Plugin URI: http://www.paulswarthout.com/wordpress/
   Description: A WordPress Dashboard widget, which displays information about the WordPress development environment. Since the information is displayed on the Dashboard, display items are limited by user capability. Sensitive items aren't shown to regular users.
   Version: 1.0
   Author: Paul A. Swarthout
   License: GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once(dirname(__FILE__) . '/classes/class-common-functions.php');
require_once(dirname(__FILE__) . '/classes/class-server-info.php');
require_once(dirname(__FILE__) . '/classes/class-user-info.php');
require_once(dirname(__FILE__) . '/classes/class-web-server-environment.php');
require_once(dirname(__FILE__) . '/classes/class-server-data.php');

$pas_wse_web_server = new pas_wse_web_server_environment(__FILE__);

register_deactivation_hook(__FILE__,array($pas_wse_web_server, 'web_environment_deactivate' ));

add_action('admin_enqueue_scripts',	array($pas_wse_web_server, 'web_environment_script' ));
add_action('admin_enqueue_scripts',	array($pas_wse_web_server, 'web_environment_style' ));
add_action('wp_dashboard_setup',	array($pas_wse_web_server, 'web_environment_dashboardWidgets'));

add_action( 'admin_menu',			array($pas_wse_web_server, 'web_environment_menu' ) );


