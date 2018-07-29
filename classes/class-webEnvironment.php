<?PHP
if (! class_exists('Dev_Environment') ) {
	class Dev_Environment {
		private $stringTable;
		private $debugging;
		private $plugin_directory;
		private $versions;
		private $serverData;
		public  $bHideMenu;

		function __construct($pluginFolder, $pluginBasename) {
			global $stringTable;
			global $serverData;

			$this->plugin_directory = $pluginFolder;
			$this->bHideMenu = get_option('pasVersions_hideMenu', false);

			$serverData = new serverInfo();

			add_action('admin_enqueue_scripts', Array($this, 'pasVersions_styles') );

			add_action('admin_enqueue_scripts', array($this, 'pas_version_script' ));
			add_action('wp_ajax_hideMenuOption', array($this, 'hideMenuOption'));
			add_action('wp_ajax_pasVersion_saveOptions', array($this, 'pasVersion_saveOptions'));
			add_action('wp_ajax_pas_version_reveal_menu', array($this, 'revealMenuOption'));
			add_action('wp_ajax_pasVersion_initialize', array($this, 'pas_Versions_initialize'));
			add_action('wp_dashboard_setup', array($this, 'pas_version_dashboard_widgets'));
		}
		function getConstant($constantName, $defaultReturn = false) {
			if (defined($constantName)) {
				return constant($constantName);
			} else {
				return false;
			}
		}

		function pas_version_script() {
			wp_enqueue_script( 'pas_version_scripts', $this->plugin_directory . '/js/pas_version_scripts.js' . ($this->getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
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
			if (defined('PHP_OS')) { $versions['PHP_OS'] = constant('PHP_OS'); }

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
													'initialState'	=> 'hidden',
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
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add(	[	'itemName'			=> 'FTP_SSL',
													'text'					=> 'FTP Use SSL: ',
													'data'					=> &$versions,
													'initialState'	=> 'hidden',
													'capability'		=> 'manage_options',
													'colorIfTrue'		=> ''
												]);
			$serverData->add( [ 'itemName'			=> 'PHP_OS',
													'text'					=> 'Server OS: ',
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
													'text'					=> 'Your browser: ',
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
//						echo "<p class='attributeEntry'>";
						echo "<span class='pvItemHeading'>";
						echo "<nobr>" . $item . "<span class='dots'>...............................................................................</span></nobr>";
						echo "</span>";
						if (! $isVisible) {
							echo "<span class='pvItemValueHidden' "
							   . "      onclick='javascript:pvShowItem(this, \"" . $source[$serverKey] . "\");'"
								 . "      onmouseover='javascript:pvShowHelp(this);' "
								 . "      onmouseout='javascript:pvHideHelp(this);' >";
							echo "click to reveal";
							echo "</span>";
						} else {
							echo "<span class='pvItemValueVisible'>";
							echo $source[$serverKey];
							echo "</span>";
						}
						echo "<br>";
					}
				}
			}

			echo "<hr>";
			echo "<table style='border:0pt;width:100%;'><tr>";
			echo "<td style='text-align:left;font-family:courier-new;font-size:10pt;'>";
			echo "<a href='http://paulswarthout.com/index.php/wordpress/'>PaulSwarthout.com/wordpress</a>";
			echo "</td>";
			echo "</tr></table>";
		}
	}
}
