<?php
namespace website_summary;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class class_pas_demo_mode {
	private $plugin_directory;
	public  $demo_user;
	public	$demo_capability;
	public	$current_user;
	function __construct($args) {
		$this->plugin_directory = (array_key_exists('pluginDirectory', $args) ? $args['pluginDirectory'] : ['path' => '', 'url'=>'']);
		$this->demo_user = constant("DEMO_USER");
		$this->demo_capability = constant("DEMO_CAPABILITY");
	}

	function inDemoMode() {
		$return = false;
		$this->current_user = wp_get_current_user()->user_login;
		if ($this->current_user == $this->demo_user) {
			$return = true;
		}
		return $return;
	}
	function getDemoUser() {
		if ($this->inDemoMode()) {
			return $this->demo_user;
		} else {
			return null;
		}
	}

	function getDemoCap() {
		if ($this->inDemoMode()) {
			return $this->demo_capability;
		} else {
			return "manage_options";
		}
	}

	function no_profile_access() {
		$this->current_user = wp_get_current_user()->user_login;
		if ($this->current_user == "demo") {
			if (strpos ($_SERVER ['REQUEST_URI'] , 'wp-admin/profile.php' )){
				wp_redirect(get_option('siteurl') . "/wp-admin");
				exit;
			}
		}
	}

}