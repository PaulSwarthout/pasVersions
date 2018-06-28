<?php
   /*
   Plugin Name: Web Environment Version Information
   Plugin URI: http://www.paulswarthout.com/index.php/wordpress/
   Description: A plugin to display information about the Wordpress Web environment. This can be useful for both development and production. The output from this plugin appears on the Wordpress Dashboard only.
   Version: 1.1
   Author: Paul A. Swarthout
   License: GPL2
   */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

register_deactivation_hook(__FILE__, 'pas_version_deactivate' );

add_action('admin_menu', 'show_php_version_menu' );
add_action('admin_enqueue_scripts', 'pas_version_script' );
add_action('wp_ajax_hideMenuOption', 'hideMenuOption');
add_action('wp_ajax_pas_version_reveal_menu', 'revealMenuOption');
add_action('wp_dashboard_setup', 'pas_version_dashboard_widgets');

function getConstant($c) {
	if (defined($c)) {
		return constant($c);
	} else {
		return false;
	}
}

function pas_version_script() {
	wp_enqueue_script( 'pas_version_scripts', plugin_dir_url( __FILE__ ) . 'js/pas_version_scripts.js' . (getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
}

function show_php_version_menu() {
	if (get_option('pas_version_hide_menu', false) == false) {
		add_menu_page( 'VERSIONS', 'VERSIONS INFO', 'manage_options', 'pasVersionInfo', 'pasVersionInfo');
	}
}

function hideMenuOption() {
	update_option('pas_version_hide_menu', true);
	wp_redirect(admin_url("index.php"));
	exit;
}
function revealMenuOption() {
	delete_option('pas_version_hide_menu');
	exit;
}

function pas_version_deactivate() {
	delete_option('pas_version_hide_menu');
}

function pasVersionInfo() {
	echo "<div style='width:50%;border:solid 1pt #990000;padding:10px;'>";
	echo "<h2>Web Environment Version Information</h2>";
	echo "Thank you for installing the Web Environment Version Information plugin. This plugin displays the version information for your web environment as a dashboard widget.";
	echo "If you do not see it, please go to the 'Screen Options' pull down tab at the top of your Wordpress Dashboard and click to open the Screen Options window.";
	echo "Once there, you will see a check box for 'Web Environment'. Click to check that box and the Web Environment dashboard widget will display on your dashboard.";
	echo "<br><br>";
	echo "Now that you know where to find the information, if you think that you'll remember that, you can check the box below to hide this menu option (I know how cluttered the dashboard menu can get).<br>";

	echo "<br><input type='checkbox' value='" . get_option('pas_version_hide_menu', 'HIDE MENU') . "' name='hide_menu' onclick='javascript:hideMenu(this);'> Hide VERSIONS INFO Menu<br>";
	echo "</div>";
}

function pas_version_dashboard_widgets() {
	global $wp_meta_boxes;
	 
	wp_add_dashboard_widget('pas_version_widget', 'Web Environment', 'pas_version_dashboard');
}

function pas_version_dashboard() {
	global $wp_version;
	global $wpdb;

	$isql = "select version() as 'version';";
	$results = $wpdb->get_results($isql);
	$mysqlVersion = explode("-", $results[0]->version);
	$mysqlVersion = $mysqlVersion[0];

	$versions = Array();

	$versions['WORDPRESS_VERSION'] = $wp_version;
	$versions['PHP_VERSION'] = phpversion();
	$versions['MYSQL_VERSION'] = $mysqlVersion;
	$versions['DB_HOST'] = constant('DB_HOST');
	$versions['DB_USER'] = constant('DB_USER');
	$versions['DB_PASS'] = constant('DB_PASSWORD');
	$versions['DB_NAME'] = constant('DB_NAME');
	$versions['WP_DEBUG'] = (getConstant('WP_DEBUG') === true ? "<font style='color:red;background-color:white;'>Enabled</font>" : "Disabled");
	$versions['WP_ALLOW_MULTISITE'] = (getConstant('WP_ALLOW_MULTISITE') === true ? "Yes" : "No");
	$versions['CURRENT_THEME'] = wp_get_theme()->__toString();


	$serverInfoAttributes = Array( 
							  'WORDPRESS_VERSION' => Array('text' => 'Wordpress Version: ', 'array'=>$versions, 'inithide' => '' ),
							  'WP_DEBUG' => Array ('text' => 'Wordpress Debug: ', 'array'=>$versions, 'inithide' => '' ),
							  'WP_ALLOW_MULTISITE' => Array ('text' => 'Wordpress Allow Multisite: ', 'array'=>$versions, 'inithide' => '' ),
							  'SERVER_SOFTWARE' => Array ('text' => 'Server Software: ', 'array' => $_SERVER, 'inithide' => '') ,
							  'SERVER_NAME' => Array ('text' => 'Server Name: ', 'array' => $_SERVER, 'inithide' => '') , 
							  'SERVER_ADDR' => Array ('text' => 'Server Address: ', 'array' => $_SERVER, 'inithide' => '') ,
							  'SERVER_ADMIN' => Array ('text' => 'Server Admin: ', 'array' => $_SERVER, 'inithide' => '') ,
							  'PHP_VERSION' => Array ('text' => 'PHP Version: ', 'array'=>$versions, 'inithide' => '' ),
		                      'MYSQL_VERSION' => Array ('text' => 'MySQL Version: ', 'array'=>$versions, 'inithide' => '' ),
							  'DB_HOST' => Array ('text' => 'Database Host: ', 'array'=>$versions, 'inithide' => '' ),
							  'DB_USER' => Array ('text' => 'Database User: ', 'array'=>$versions, 'inithide' => '' ),
							  'DB_PASS' => Array ('text' => 'Database Password: ', 'array'=>$versions, 'inithide' => 'hide' ),
							  'DB_NAME' => Array ('text' => 'Database Name: ', 'array'=>$versions, 'inithide' => '' ),
							  'CURRENT_THEME' => Array ('text' => 'Current Active Theme: ', 'array'=>$versions, 'inithide' => ''),
							  'REMOTE_ADDR' => Array ('text' => 'Your IP: ', 'array' => $_SERVER, 'inithide' => '') ,
							  'HTTP_USER_AGENT' => Array ('text' => 'User Agent: ', 'array' => $_SERVER, 'inithide' => '') 
							);
	
	foreach ($serverInfoAttributes as $serverKey => $datablock) {
		$item = $datablock['text'];
		$source = $datablock['array'];
		$initHide = (strtoupper($datablock['inithide']) == "HIDE" ? true : false);

		if (array_key_exists($serverKey, $source)) {
			if ($initHide) {
				echo $item . "<span style='padding:0 10px 0 3px;background-color:white;color:black;border:0pt;' onclick='javascript:pvShowItem(this, \"" . $source[$serverKey] . "\");'>(click to reveal)</span><br>";
			} else {
				echo $item . "<b>" . $source[$serverKey] . "</b><br>";
			}

		}
	}
	echo "<hr>";
	echo "<table style='border:0pt;width:100%;'><tr>";
	echo "<td style='text-align:left;font-family:courier-new;font-size:10pt;'>";
	echo "<a href='http://paulswarthout.com/index.php/wordpress/'>PaulSwarthout.com/wordpress</a>";
	echo "</td><td style='text-align:right;font-family:courier-new;font-size:10pt;'>";
	if (get_option('pas_version_hide_menu', false) == true) {
		echo "<a href='javascript:void(0);' onclick='javascript:revealMenu();'>reveal menu</a>";
	} else { echo "&nbsp;"; }
	echo "</tr></table>";
}


?>