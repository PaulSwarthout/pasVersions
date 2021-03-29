<?php
/*
   Plugin Name: Website Summary
   Plugin URI: http://www.paulswarthout.com/website-summary
   Description: A WordPress Dashboard widget, which displays a summary of information about your Wordpress website. Additional information is displayed on a dashboard menu page.
   Version: 1.2
   Author: Paul A. Swarthout
   Author URI: http://www.paulswarthout.com
   License: GPL2
*/

namespace website_summary;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Detects symbolically linked folder.
require( dirname( __FILE__ ) . '/includes/symlinks.php');

require_once( dirname( __FILE__ ) . '/classes/class-common-functions.php' );
require_once( dirname( __FILE__ ) . '/classes/class-server-info.php' );
require_once( dirname( __FILE__ ) . '/classes/class-user-info.php' );
require_once( dirname( __FILE__ ) . '/classes/class-web-server-environment.php' );
require_once( dirname( __FILE__ ) . '/classes/class-server-data.php' );

$pas_wse_web_server = new pas_wse_web_server_environment( __FILE__ );

register_deactivation_hook(__FILE__,array($pas_wse_web_server, 'web_environment_deactivate' ));

add_action('admin_enqueue_scripts',	array($pas_wse_web_server, 'web_environment_script' ));
add_action('admin_enqueue_scripts',	array($pas_wse_web_server, 'web_environment_style' ));
add_action('wp_dashboard_setup',	array($pas_wse_web_server, 'web_environment_dashboardWidgets'));

add_action( 'admin_menu',			array($pas_wse_web_server, 'web_environment_menu' ) );
