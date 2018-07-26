<?PHP
if (! class_exists('Web_Environment') ) {
	class Web_Environment {
		private $stringTable;
		private $debugging;
		private $plugin_directory;
		private $options;
		private $defaultOptions;
		private $versions;
		private $serverData;
		public $bInitComplete;
		public $bDashboard;
		public $bShowSensitive;
		public $bHideMenu;


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
		function __construct($pluginFolder, $pluginBasename) {
			global $stringTable;
			global $serverData;

			$this->defaultOptions = PASVERSIONS_DEFAULT_OPTIONS;
			$this->debugging = $this->getConstant('WP_DEBUG', false);
			$this->plugin_directory = $pluginFolder;
			$this->options =
				$this->parseOptions(
					[ 'optionString' => get_option("pasVersionsOptions", $this->defaultOptions),
				 		'primaryDelimiter' => '/;/',
						'secondaryDelimiter' => '/:/'
					] );
			$this->bInitComplete = ($this->options['INITCOMPLETE'] === "YES" ? true : false);
			$this->bDashboard = ($this->options['DASHBOARD'] === "YES" ? true : false);
			$this->bShowSensitive = ($this->options['SHOWSENSITIVE'] === "YES" ? true : false);
			$this->bHideMenu = ($this->options['HIDEMENU'] === "YES" ? true : false);

			$serverData = new serverInfo();

			if (! $this->bInitComplete) {
				update_option("pasVersionOptions", $this->defaultOptions);
			}

			// Add initialize line to the plugin listing.
			if (! $this->bInitComplete) {
				add_filter( "plugin_action_links_" . $pluginBasename, Array($this, 'plugin_add_settings_link' ));
			}

			add_action('admin_enqueue_scripts', Array($this, 'pasVersions_styles') );

			add_action('admin_menu', array($this, 'show_php_version_menu' ));
			add_action('admin_enqueue_scripts', array($this, 'pas_version_script' ));
			add_action('wp_ajax_hideMenuOption', array($this, 'hideMenuOption'));
			add_action('wp_ajax_pasVersion_saveOptions', array($this, 'pasVersion_saveOptions'));
			add_action('wp_ajax_pas_version_reveal_menu', array($this, 'revealMenuOption'));
			add_action('wp_ajax_pasVersion_initialize', array($this, 'pas_Versions_initialize'));
			add_action('wp_dashboard_setup', array($this, 'pas_version_dashboard_widgets'));
		}
		function pasVersions_styles() {
			wp_enqueue_style('web-environment', $this->plugin_directory . "css/styles.css" . ($this->debugging ? '?v=' . rand(1,99999) : ''), false);
		}
		function plugin_add_settings_link( $links ) {
				$menuURL = menu_page_url('pasVersionInfo');
				if (strlen($menuURL) > 0) {
					$settings_link = '<a href="' . $menuURL . '">Initialize</a>';
					array_push( $links, $settings_link );
				}
				return $links;
		}
		function getConstant($constantName, $defaultReturn = false) {
			if (defined($constantName)) {
				return constant($constantName);
			} else {
				return false;
			}
		}

		function pasVersion_saveOptions() {
			if (isset($_POST['pasVersionOptions'])) {
				update_option("pasVersionOptions", $_POST['pasVersionOptions']);
				wp_redirect(admin_url("index.php"));
				exit;
			}
		}
		function pas_version_script() {
			wp_enqueue_script( 'pas_version_scripts', $this->plugin_directory . '/js/pas_version_scripts.js' . ($this->getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
		}

		function show_php_version_menu() {
			if ($this->pasVersionOptions['HIDEMENU'] == "NO") {
				add_menu_page( 'VERSIONS', 'Web Environment', 'manage_options', 'pasVersionInfo', Array($this, 'pasVersionInfo'), 1, 1);
			}
		}

		function pas_version_deactivate() {
			delete_option('pas_version_hide_menu');
			delete_option('pasVersionInitComplete');
			delete_option('pasVersion_dashboard_widget');
			delete_option('pasVersion_Init_Complete');
			delete_option('pasVersionOptions');
		}

		function pasVersionInfo() {
			global $stringTable;

			if (! current_user_can('manage_options')) {
				wp_die("You are not authorized to access this page.");
			}

			echo $stringTable->getString('THANK YOU FOR INSTALLING');

			echo "<p>";
			echo "<form id='pasVersionOptionsForm'>";
			echo "<ol type='1'>";
			echo "<li><input type='radio' name='hideMenu' " . ($this->pasVersionOptions['HIDEMENU'] == "YES" ? " checked " : "") . " value='HIDEMENU:YES;'>&nbsp;HIDE DASHBOARD MENU&nbsp;&nbsp;<input type='radio' name='hideMenu'" . ($this->pasVersionOptions['HIDEMENU'] == "NO" ? " checked " : "") . " value='HIDEMENU:NO;'>&nbsp;SHOW DASHBOARD MENU</li>";
			echo "<li><input type='radio' name='displayWhere' " . ($this->pasVersionOptions['DASHBOARD'] == "YES" ? " checked " : "") . " value='DASHBOARD:YES;'>&nbsp;SHOW AS DASHBOARD WIDGET&nbsp;&nbsp;<input type='radio' name='displayWhere' " . ($this->pasVersionOptions['DASHBOARD'] == "NO" ? " checked " : "") . " value='DASHBOARD:NO;'>&nbsp;SHOW ON DASHBOARD MENU PAGE</li>";
			echo "<li><input type='radio' name='displayHow' " . ($this->pasVersionOptions['SHOWSENSITIVE'] == "YES" ? " checked " : "") . " value='SHOWSENSITIVE:YES;'>&nbsp;SHOW SENSITIVE INFO&nbsp;&nbsp;<input type='radio' name='displayHow' " . ($this->pasVersionOptions['SHOWSENSITIVE'] == "NO" ? " checked " : "") . " value='SHOWSENSITIVE:NO;'>&nbsp;DO NOT SHOW SENSITIVE INFO</li>";
			echo "</ol>";
			echo "<br>Note: You cannot display the information on a Dashboard Menu page (#2) if you Hide the Dashboard Menu (#1)<br><br>";
			echo "<INPUT TYPE='BUTTON' VALUE='Save Options'  onclick='javascript:pasVersion_saveOptions(this.form);'>";
			echo "<INPUT TYPE='HIDDEN' value='INITCOMPLETE:YES;' NAME='initComplete'>";
			echo "</form>";
		}

		function pas_version_dashboard_widgets() {
			global $wp_meta_boxes;
			if ($this->pasVersionOptions['INITCOMPLETE'] == "YES") {
				if ($this->pasVersionOptions['DASHBOARD'] == "YES") {
					wp_add_dashboard_widget('pas_version_widget', 'Web Environment', Array($this, 'pas_version_dashboard'));
				}
			} else {
				wp_add_dashboard_widget('pas_version_widget', 'Web Environment', Array($this, 'pasVersion_dashboardWidget_blank'));
			}
		}
		function pasVersion_dashboardWidget_blank() {
			echo "The Web Environment Dashboard Widget is blank until you initialize it. Go to the Web Environment Plugin on the plugins page and click the Initialize Link.";
		}

		function initializeEnvironmentData() {
			global $wpdb;
			global $wp_version;
			global $versions;
			global $serverData;

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

			$versions['WORDPRESS_VERSION'] = $wp_version;
			$versions['PHP_VERSION'] = phpversion();

			if (defined('DB_HOST')) {	$versions['DB_HOST'] = constant('DB_HOST'); }
			if (defined('DB_USER')) { $versions['DB_USER'] = constant('DB_USER'); }
			if (defined('DB_PASSWORD')) { $versions['DB_PASSWORD'] = constant('DB_PASSWORD'); }
			if (defined('DB_NAME')) { $versions['DB_NAME'] = constant('DB_NAME'); }
			if (defined('WP_DEBUG')) { $versions['WP_DEBUG'] = (constant('WP_DEBUG') === true ? "<font style='color:red;background-color:white;'>Enabled</font>" : "Disabled"); }
			if (defined('WP_ALLOW_MULTISITE')) { $versions['WP_ALLOW_MULTISITE'] = (constant('WP_ALLOW_MULTISITE') === true ? "Yes" : "No"); }
			if (defined('FS_METHOD')) { $versions['FS_METHOD'] = constant('FS_METHOD'); }
			if (defined('FTP_BASE')) { $versions['FTP_BASE'] = constant('FTP_BASE'); }
			if (defined('FTP_CONTENT_DIR')) { $versions['FTP_CONTENT_DIR'] = constant('FTP_CONTENT_DIR'); }
			if (defined('FTP_PLUGIN_DIR')) { $versions['FTP_PLUGIN_DIR'] = constant('FTP_PLUGIN_DIR'); }
			if (defined('FTP_PUBKEY')) { $versions['FTP_PUBKEY'] = constant('FTP_PUBKEY'); }
			if (defined('FTP_PRIKEY')) { $versions['FTP_PRIKEY'] = constant('FTP_PRIKEY'); }
			if (defined('FTP_USER')) { $versions['FTP_USER'] = constant('FTP_USER'); }
			if (defined('FTP_PASS')) { $versions['FTP_PASS'] = constant('FTP_PASS'); }
			if (defined('FTP_HOST')) { $versions['FTP_HOST'] = constant('FTP_HOST'); }
			if (defined('FTP_SSL')) { $versions['FTP_SSL'] = (constant('FTP_SSL') === true ? "Yes" : "No"); }

			$versions['CURRENT_THEME'] = wp_get_theme()->__toString();


			$serverData->add(	[	'itemName'			=> 'WORDPRESS_VERSION',
													'text'					=> 'WordPress Version: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'WP_DEBUG',
													'text'					=> 'WordPress Debug: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> 'red'
												]);
			$serverData->add(	[	'itemName'			=> 'WP_ALLOW_MULTISITE',
													'text'					=> 'WordPress Allow Multisite: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> 'red'
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_BASE',
													'text'					=> 'WordPress Root: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_CONTENT_DIR',
													'text'					=> 'WordPress Content Folder: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_PLUGIN_DIR',
													'text'					=> 'WordPress Plugin Folder: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_PUBKEY',
													'text'					=> 'FTP SSH Public Key Path: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_PRIKEY',
													'text'					=> 'FTP SSH Private Key Path: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FS_METHOD',
													'text'					=> 'FTP Connection Method',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_USER',
													'text'					=> 'FTP Username: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_PASS',
													'text'					=> 'FTP Password: ',
													'data'					=> &$versions,
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_HOST',
													'text'					=> 'FTP Host: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_SSL',
													'text'					=> 'FTP Use SSL: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'SERVER_SOFTWARE',
													'text'					=> 'Server Software: ',
													'data'					=> &$_SERVER,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'SERVER_NAME',
													'text'					=> 'Server Name: ',
													'data'					=> &$_SERVER,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'PHP_VERSION',
													'text'					=> 'PHP Version: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add( [ 'itemName'			=> 'MYSQL_VERSION',
													'text'					=> 'MySQL Database Version: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												] );
			$serverData->add(	[	'itemName'			=> 'DB_HOST',
													'text'					=> 'Database Host: ',
													'data'					=> &$versions,
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'DB_USER',
													'text'					=> 'Database User: ',
													'data'					=> &$versions,
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'DB_PASSWORD',
													'text'					=> 'Database Password: ',
													'data'					=> &$versions,
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'DB_NAME',
													'text'					=> 'Database Name: ',
													'data'					=> &$versions,
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'CURRENT_THEME',
													'text'					=> 'Currect Active Theme: ',
													'data'					=> &$versions,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'SERVER_ADDR',
													'text'					=> 'Server Address: ',
													'data'					=> &$_SERVER,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'SERVER_ADMIN',
													'text'					=> 'Server Admin: ',
													'data'					=> &$_SERVER,
													'initialState'	=> 'visible',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'REMOTE_ADDR',
													'text'					=> 'Your IP: ',
													'data'					=> &$_SERVER,
													'initialState'	=> 'visible',
													'capability'		=> 'read',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'HTTP_USER_AGENT',
													'text'					=> 'User Agent: ',
													'data'					=> &$_SERVER,
													'initialState'	=> 'visible',
													'capability'		=> 'read',
													'colorIfTrue'		=> ''
												]);

		}
		function dumpEnvironmentData() {
			global $wp_version;
			global $versions;
			global $serverData;

			foreach ($serverData->serverAttributes as $serverKey => $dataBlock) {
				$item = $dataBlock['text'];
				$source = $dataBlock['data'];
				$isVisible = (strtoupper($dataBlock['initialState']) === "VISIBLE" ? true : false);
				$capability = $dataBlock['capability'];

				if (current_user_can($capability)) {
					if (array_key_exists($serverKey, $source)) {
						echo "<p class='attributeEntry'>";
						if (! $isVisible) {
							echo $item . "<span class='hiddenAttribute' "
							           . "      onclick='javascript:pvShowItem(this, \"" . $source[$serverKey] . "\");'>";
							echo "click to reveal";
							echo "</span>";
						} else {
							echo $item . "<b>" . $source[$serverKey] . "</b><br>";
						}
						echo "</p>";
					}
				}
			}

			echo "<hr>";
			echo "<table style='border:0pt;width:100%;'><tr>";
			echo "<td style='text-align:left;font-family:courier-new;font-size:10pt;'>";
			echo "<a href='http://paulswarthout.com/index.php/wordpress/'>PaulSwarthout.com/wordpress</a>";
			echo "</td><td style='text-align:right;font-family:courier-new;font-size:10pt;'>";
			if ($this->bHideMenu) {
				echo "<a href='javascript:void(0);' onclick='javascript:revealMenu();'>reveal menu</a>";
			}
			echo "</tr></table>";
		}
	}
}
