<?PHP
if (! class_exists('pasVersion') ) {

	class pasVersion {
		var $plugin_directory;
		var $stringTable;
		var $pasVersionOptions;
		var $bInitComplete;
		var $debugging;

		function __construct($pluginFolder, $pluginBasename) {
			global $stringTable;

			$this->debugging = (defined('WP_DEBUG') ? constant('WP_DEBUG') : false);
			$this->plugin_directory = $pluginFolder;
			$this->pasVersionOptions = $this->makeArray([ 'input_string' => get_option("pasVersionOptions", "DASHBOARD:YES;SHOWSENSITIVE:NO;HIDEMENU:NO;INITCOMPLETE:NO;"),
																										'primaryDelimiter' => '/;/', 
																										'secondaryDelimiter' => '/:/'
																									] );
			if ($this->pasVersionOptions['INITCOMPLETE'] == "NO") {
				update_option("pasVersionOptions", "DASHBOARD:YES;SHOWSENSITIVE:NO;HIDEMENU:NO;INITCOMPLETE:NO;");
			}
			$this->bInitComplete = ($this->pasVersionOptions['INITCOMPLETE'] == "YES" ? true : false);

			$stringTable = new pasVersions_StringTable();

			if ($this->pasVersionOptions['INITCOMPLETE'] == "NO") {
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
		function makeArray($args) {
			$inputString = $args['input_string'];
			$primaryDelimiter = $args['primaryDelimiter'];
			$secondaryDelimiter = $args['secondaryDelimiter'];

			$outputArray = Array();
			$dbg = new aresDebug();
			$primary = preg_split($primaryDelimiter, $inputString);
			for ($ndx = 0; $ndx < count($primary); $ndx++) {
				if (strlen($primary[$ndx]) > 0) {
					$secondary = preg_split($secondaryDelimiter, $primary[$ndx]);

					if (count($secondary) > 0) {
						$outputArray[$secondary[0]] = $secondary[1];
					}
				}
			}
			return $outputArray;
		}
		function getConstant($c) {
			if (defined($c)) {
				return constant($c);
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
		function pas_version_dashboard() {
			global $wp_version;
			global $wpdb;

			$isql = "select version() as 'version';";
			$results = $wpdb->get_results($isql);
			$mysqlVersion = explode("-", $results[0]->version);
			$mysqlVersion = $mysqlVersion[0];

			$bShowOnDashboard = ($this->pasVersionOptions['DASHBOARD'] == 'YES' ? true : false);
			$bShowSensitiveInfo = ($this->pasVersionOptions['SHOWSENSITIVE'] == 'YES' ? true : false);

			$versions = Array();

			$versions['WORDPRESS_VERSION'] = $wp_version;
			$versions['PHP_VERSION'] = phpversion();
			$versions['MYSQL_VERSION'] = $mysqlVersion;
			if (defined('DB_HOST')) {	$versions['DB_HOST'] = constant('DB_HOST'); }
			if ($bShowSensitiveInfo) {
				if (defined('DB_USER')) { $versions['DB_USER'] = constant('DB_USER'); }
				if (defined('DB_PASSWORD')) { $versions['DB_PASSWORD'] = constant('DB_PASSWORD'); }
				if (defined('DB_NAME')) { $versions['DB_NAME'] = constant('DB_NAME'); }
			}
			if (defined('WP_DEBUG')) { $versions['WP_DEBUG'] = (constant('WP_DEBUG') === true ? "<font style='color:red;background-color:white;'>Enabled</font>" : "Disabled"); }
			if (defined('WP_ALLOW_MULTISITE')) { $versions['WP_ALLOW_MULTISITE'] = (constant('WP_ALLOW_MULTISITE') === true ? "Yes" : "No"); }
			if (defined('FS_METHOD')) { $versions['FS_METHOD'] = constant('FS_METHOD'); }
			if (defined('FTP_BASE')) { $versions['FTP_BASE'] = constant('FTP_BASE'); }
			if (defined('FTP_CONTENT_DIR')) { $versions['FTP_CONTENT_DIR'] = constant('FTP_CONTENT_DIR'); }
			if (defined('FTP_PLUGIN_DIR')) { $versions['FTP_PLUGIN_DIR'] = constant('FTP_PLUGIN_DIR'); }
			if (defined('FTP_PUBKEY')) { $versions['FTP_PUBKEY'] = constant('FTP_PUBKEY'); }
			if (defined('FTP_PRIKEY')) { $versions['FTP_PRIKEY'] = constant('FTP_PRIKEY'); }
			if ($bShowSensitiveInfo) {
				if (defined('FTP_USER')) { $versions['FTP_USER'] = constant('FTP_USER'); }
				if (defined('FTP_PASS')) { $versions['FTP_PASS'] = constant('FTP_PASS'); }
				if (defined('FTP_HOST')) { $versions['FTP_HOST'] = constant('FTP_HOST'); }
			}
			if (defined('FTP_SSL')) { $versions['FTP_SSL'] = (constant('FTP_SSL') === true ? "Yes" : "No"); }

			$versions['CURRENT_THEME'] = wp_get_theme()->__toString();

			$serverInfoAttributes = Array( 
										'WORDPRESS_VERSION' => Array('text' => 'WordPress Version: ', 'array'=>$versions, 'inithide' => '' ),
										'WP_DEBUG' => Array ('text' => 'WordPress Debug: ', 'array'=>$versions, 'inithide' => '' ),
										'WP_ALLOW_MULTISITE' => Array ('text' => 'WordPress Allow Multisite: ', 'array'=>$versions, 'inithide' => '' ),
										'FTP_BASE' => Array('text' => 'WordPress Root: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_CONTENT_DIR' => Array('text' => 'WordPress Content Folder: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_PLUGIN_DIR' => Array('text' => 'WordPress Plugin Folder: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_PUBKEY' => Array('text' => 'FTP SSH Public Key Path: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_PRIKEY' => Array('text' => 'FTP SSH Private Key Path: ', 'array'=>$versions, 'inithide' => ''),
										'FS_METHOD' => Array('text' => 'FTP Connection Method: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_USER' => Array('text' => 'FTP Username: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_PASS' => Array('text' => 'FTP Password: ', 'array'=>$versions, 'inithide' => 'hide'),
										'FTP_HOST' => Array('text' => 'FTP Host: ', 'array'=>$versions, 'inithide' => ''),
										'FTP_SSL' => Array('text' => 'FTP Use SSL: ', 'array'=>$versions, 'inithide' => ''),
										'SERVER_SOFTWARE' => Array ('text' => 'Server Software: ', 'array' => $_SERVER, 'inithide' => '') ,
										'SERVER_NAME' => Array ('text' => 'Server Name: ', 'array' => $_SERVER, 'inithide' => '') , 
										'PHP_VERSION' => Array ('text' => 'PHP Version: ', 'array'=>$versions, 'inithide' => '' ),
															'MYSQL_VERSION' => Array ('text' => 'MySQL Version: ', 'array'=>$versions, 'inithide' => '' ),
										'DB_HOST' => Array ('text' => 'Database Host: ', 'array'=>$versions, 'inithide' => '' ),
										'DB_USER' => Array ('text' => 'Database User: ', 'array'=>$versions, 'inithide' => '' ),
										'DB_PASSWORD' => Array ('text' => 'Database Password: ', 'array'=>$versions, 'inithide' => 'hide' ),
										'DB_NAME' => Array ('text' => 'Database Name: ', 'array'=>$versions, 'inithide' => '' ),
										'CURRENT_THEME' => Array ('text' => 'Current Active Theme: ', 'array'=>$versions, 'inithide' => ''),
										'SERVER_ADDR' => Array ('text' => 'Server Address: ', 'array' => $_SERVER, 'inithide' => '') ,
										'SERVER_ADMIN' => Array ('text' => 'Server Admin: ', 'array' => $_SERVER, 'inithide' => '') ,
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
			if ($this->pasVersionOptions['HIDEMENU'] == "YES") {
				echo "<a href='javascript:void(0);' onclick='javascript:revealMenu();'>reveal menu</a>";
			}
			echo "</tr></table>";
		}
	}
}
