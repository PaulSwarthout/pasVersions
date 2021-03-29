<?PHP

namespace website_summary;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class pas_wse_web_server_environment {
	private $plugin_directory;
	private $library;
	private $plugin_file;
	private $cacheBuster;
	private $parameters;

	function __construct($FILE) {
		$this->plugin_file = $FILE;
		$this->plugin_directory =
			[
				'path'	=>	plugin_dir_path	( $FILE ),
				'url'	=>	plugin_dir_url	( $FILE ),
			];
		$this->library = new pas_wse_common_functions();
		$this->cacheBuster = (constant('WP_DEBUG') ? "?cacheBuster=" . time() . "&" : "");
		$this->parameters =
			[
				'plugin_directory'	=>	$this->plugin_directory,
				'libraryFunctions'	=>	$this->library,
			];
	}

	function web_environment_script() {
		wp_register_script('pasVersions_scripts', $this->plugin_directory['url'] . 'js/pasVersions.js' . $this->cacheBuster, '', '5.1', true);
		wp_enqueue_script('pasVersions_scripts');
	}
	function web_environment_style() {
		wp_enqueue_style( 'pasVersions_styles', $this->plugin_directory['url'] . 'css/style.css' . $this->cacheBuster, false);
	}

	function web_environment_deactivate() {
		delete_option('pasVersions_firstLoad');
	}

	function web_environment_menu() {
		$userlogin = "";
		if (defined("DEMO_USER")) {
			$userlogin = strtolower(constant("DEMO_USER"));
		}
		if ($userlogin == strtolower(wp_get_current_user()->user_login) && defined("DEMO_CAPABILITY")) {
			$capability = constant("DEMO_CAPABILITY");
		} else {
			$capability = "manage_options";
		}

		$pageTitle = 'web-environment';
		$menuTitle = 'Website Summary';
		$menuSlug  = $pageTitle;
		$icon		= '';
		$position	= null;

		add_menu_page(
			$pageTitle,
			$menuTitle,
			$capability,
			$menuSlug,
			array($this, 'web_environment_display'),
			$icon,
			$position);

		$subPageTitle = "Web-Env-Dashboard";
		$subMenuTitle = "Dashboard";
		$subMenuSlug  = "web-env-dashboard";
		add_submenu_page( $menuSlug, $subPageTitle, $subMenuTitle, $capability, $subMenuSlug, array($this, 'wp_dashboard'));
	}

	function web_environment_display() {
		echo "<div id='tooSmallNote'>";
		echo "This information cannot be displayed when the screen width is less than 240 pixels wide.";
		echo "</div>";
		echo "<div id='menuPage'>";
		$envData = new pas_wse_server_data($this->parameters);
		$envData->initializeEnvironmentData();
		$envData->dumpEnvironmentData();
		unset($envData);
		echo "</div>";
	}
	function web_environment_dashboardWidgets() {
		global $wp_meta_boxes;

		wp_add_dashboard_widget('pasVersions_widget', 'Website Summary', array($this, 'web_environment_dashboard'));
	}
	function web_environment_dashboard() {
		global $wp_version;
		global $wpdb;

		echo "<div id='tooSmallNote'>";
		echo "This information cannot be displayed when the screen width is less than 240 pixels wide.";
		echo "</div>";
		echo "<div id='dashboardPage'>";
		$envData = new pas_wse_server_data($this->parameters);
		$envData->initializeEnvironmentData();
		$envData->dumpEnvironmentData();
		echo "</div>";
		unset($envData);
	}
	function wp_dashboard() {
		echo "What do you want to see in your website summary?<br>";
		$dropDownButton = "<img src='" . $this->plugin_directory['url'] . "/assets/images/drop_down_button.png'>";
		$allConstants = get_defined_constants(true);

		$lastCategory = "";

		echo "<div id='wp_dashboard'>";
		foreach ($allConstants as $category => $constants) {
			echo "<button class='categoryHeader'>{$dropDownButton}&nbsp;{$category}</button>";
			echo "<div class='content'>";
			ksort($constants);

			foreach ($constants as $key => $constant) {
				echo "<p data-name='{$key}' data-value='{$constant}'><input type='checkbox'>{$key}</p>";
			}
			echo "</div>";
		}
		echo "</div>";
	}
}
