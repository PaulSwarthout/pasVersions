<?PHP
class pas_wse_web_server_environment {
	private $plugin_directory;
	private $library;
	private $plugin_file;

	function __construct($FILE) {
		$this->plugin_file = $FILE;
		$this->plugin_directory =
			[
				'path'	=>	plugin_dir_path	( $FILE ),
				'url'	=>	plugin_dir_url	( $FILE )
			];
		$this->library = new pas_wse_common_functions();
	}

	function web_environment_script() {
		wp_enqueue_script( 'pasVersions_scripts', $this->plugin_directory['url'] . 'js/pasVersions.js' . ($this->library->getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
	}
	function web_environment_style() {
		wp_enqueue_style( 'pasVersions_styles', $this->plugin_directory['url'] . 'css/style.css' . ($this->library->getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
	}

	function web_environment_deactivate() {
		delete_option('pasVersions_firstLoad');
	}

	function web_environment_menu() {
		add_menu_page( 	'web-environment',
						'Your Website',
						'manage_options',
						'view_web_environment',
						Array( $this, 'web_environment_display' )
					 );

	}

	function web_environment_display() {
		$args =
			[
				'pluginDirectory'	=>	$this->plugin_directory,
				'libraryFunctions'	=>	$this->library,
			];
		echo "<div id='tooSmallNote'>";
		echo "This information cannot be displayed when the screen width is less than 240 pixels wide.";
		echo "</div>";
		echo "<div id='menuPage'>";
		$envData = new pas_wse_server_data($args);
		$envData->initializeEnvironmentData();
		$envData->dumpEnvironmentData();
		unset($envData);
		echo "</div>";
	}
	function web_environment_dashboardWidgets() {
		global $wp_meta_boxes;

		wp_add_dashboard_widget('pasVersions_widget', 'Your Website', array($this, 'web_environment_dashboard'));
	}
	function web_environment_dashboard() {
		global $wp_version;
		global $wpdb;

		$args =
			[
				'pluginDirectory'	=>	$this->plugin_directory,
				'libraryFunctions'	=>	$this->library,
			];
		echo "<div id='tooSmallNote'>";
		echo "This information cannot be displayed when the screen width is less than 240 pixels wide.";
		echo "</div>";
		echo "<div id='dashboardPage'>";
		$envData = new pas_wse_server_data($args);
		$envData->initializeEnvironmentData();
		$envData->dumpEnvironmentData();
		echo "</div>";
		unset($envData);
	}
}
