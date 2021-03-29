<?PHP
namespace website_summary;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (! class_exists('pas_wse_server_data') ) {
	class pas_wse_server_data {
		private $debugging;
		private $plugin_directory;
		private $versions;
		private $serverData;
		private $libraryFunctions;

		function __construct($args) {
			$this->plugin_directory = $args['plugin_directory'];
			$this->libraryFunctions = $args['libraryFunctions'];
			$args = [
						'libraryFunctions' => $this->libraryFunctions,
						'plugin_directory' => $this->plugin_directory,
					];

			$this->serverData = new pas_wse_server_info($args);

			add_action('admin_enqueue_scripts', Array($this, 'pasVersions_styles') );
			add_action('admin_enqueue_scripts', array($this, 'pas_version_script' ));

			add_action('wp_dashboard_setup', array($this, 'pas_version_dashboard_widgets'));
		}
		function pas_version_script() {
			wp_enqueue_script( 'pas_version_scripts', $this->plugin_directory['url'] . '/js/pas_version_scripts.js' . ($this->libraryFunctions->getConstant('WP_DEBUG') !== false ? '?v=' . rand(1,999) : ''), false);
		}

		function dumpEnvironmentData() {
			$attributes = $this->serverData->getAttributesSortedByGroup();

			$lastGroup = (-1);


			foreach ($attributes as $serverKey => $dataBlock) {
				$item = $dataBlock['text'];
				$source = $dataBlock['data'];
				$isVisible = ("VISIBLE" === strtoupper($dataBlock['initialState']) ? true : false);
				$capability = $dataBlock['capability'];
				$groupValue = $dataBlock['groupValue'];

				if ($lastGroup != $groupValue && array_key_exists($serverKey, $source) ) {
					$groupHeading = "<div class='pvHeading'>" . $dataBlock['groupName'] . "</div>";
					$lastGroup = $groupValue;

					echo $groupHeading;
				}

				if (current_user_can($capability)) {
					if (array_key_exists($serverKey, $source)) {
						echo "<span class='pvLine'>";
						echo "<span class='pvItemHeading'>";
						echo "" . $item . "<span class='dots'>...............................................................................</span>";
						echo "</span>";
						if (! $isVisible) {
							echo "<span class='pvItemValueHidden' "
								 . "	onclick='javascript:pvShowItem(this, \"" . $source[$serverKey] . "\");' "
								 . ">";
							echo "click to reveal";
							echo "</span>";
						} else {
							echo "<span class='pvItemValueVisible'>";
							echo $source[$serverKey];
							echo "</span>";
						}
						echo "</span><br>";
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
		function initializeEnvironmentData() {
			global $wpdb;
			global $wp_version;

			$isql = "select version() as 'version';";
			$results = $wpdb->get_results($isql);
			$mysqlVersion = explode("-", $results[0]->version);
			$mysqlVersion = $mysqlVersion[0];

			$isql = " show variables where variable_name like 'general_log%'; ";
			$results = $wpdb->get_results($isql, ARRAY_A);
			foreach ($results as $row) {
				if ($row['Variable_name'] == "general_log") {
					$mysqlLog = ($row['Value'] == "OFF" ? "Disabled" : "<font style='color:red;background-color:white;'>Enabled</font>" );
				} else if ($row['Variable_name'] == "general_log_file") {
					$mysqlLogFile = ($mysqlLog != "Disabled" ? $row['Value'] : "");
				}
			}

			$this->versions = Array();

			$this->versions['CURRENT_THEME'] = wp_get_theme()->__toString();
			$this->serverData->setGroupings(
				[
					'Versions',
					'Logging',
					'Constants',
					'WordPress',
					'Web Server',
					'FTP',
					'Database',
					'Your Info'
				]);
			$this->versions['WORDPRESS_VERSION'] = $wp_version;
			$this->versions['PHP_VERSION'] = phpversion();
			$this->versions['MYSQL_VERSION'] = $mysqlVersion;
			$this->versions['MYSQL_GENERAL_LOG'] = $mysqlLog;
			if (isset($mysqlLogFile)) {
				$this->versions['MYSQL_GENERAL_LOG_FILE'] = $mysqlLogFile;
			}
			$this->versions['WP_ALLOW_MULTISITE'] = ($this->libraryFunctions->getConstant('WP_ALLOW_MULTISITE') === true ? "Yes" : "No");
			$this->versions['CURRENT_THEME'] = wp_get_theme()->__toString();
			$this->versions['SITE URL'] = (defined('WP_SITEURL') ? constant('WP_SITEURL') : get_bloginfo('url'));
			$this->versions['WP URL'] = (defined('WP_HOME') ? constant('WP_HOME') : get_bloginfo('wpurl'));
			$this->versions['ADM EMAIL'] = get_bloginfo('admin_email');

			$this->versions['WORDPRESS_VERSION'] = $wp_version;
			$this->versions['PHP_VERSION'] = phpversion();

			$user = new pas_wse_user_info();

			$this->versions['LOGGED-ON-USER'] = $user->name();
			$this->versions['LOGGED-ON-USER-EMAIL'] = $user->email();
			$this->versions['Install_Root'] = ABSPATH;

			unset($user);

			if (defined('DB_HOST')) {	$this->versions['DB_HOST'] = constant('DB_HOST'); }
			if (defined('DB_USER')) { $this->versions['DB_USER'] = constant('DB_USER'); }
			if (defined('DB_PASSWORD')) { $this->versions['DB_PASSWORD'] = constant('DB_PASSWORD'); }
			if (defined('DB_NAME')) { $this->versions['DB_NAME'] = constant('DB_NAME'); }
			if (defined('WP_DEBUG')) { $this->versions['WP_DEBUG'] = (constant('WP_DEBUG') === true ? "<font style='color:red;background-color:white;'>Enabled</font>" : "Disabled"); }
			if (defined('WP_DEBUG_LOG')) { $this->versions['WP_DEBUG_LOG'] = (constant('WP_DEBUG_LOG') === true ? "<font style='color:red;background-color:white;'>Enabled</font>" : "Disabled"); }
			if (defined('WP_ALLOW_MULTISITE')) { $this->versions['WP_ALLOW_MULTISITE'] = (constant('WP_ALLOW_MULTISITE') === true ? "Yes" : "No"); }
			if (defined('FS_METHOD')) { $this->versions['FS_METHOD'] = constant('FS_METHOD'); }
			if (defined('FTP_BASE')) { $this->versions['FTP_BASE'] = constant('FTP_BASE'); }
			if (defined('FTP_CONTENT_DIR')) { $this->versions['FTP_CONTENT_DIR'] = constant('FTP_CONTENT_DIR'); }
			if (defined('FTP_PLUGIN_DIR')) { $this->versions['FTP_PLUGIN_DIR'] = constant('FTP_PLUGIN_DIR'); }
			if (defined('FTP_PUBKEY')) { $this->versions['FTP_PUBKEY'] = constant('FTP_PUBKEY'); }
			if (defined('FTP_PRIKEY')) { $this->versions['FTP_PRIKEY'] = constant('FTP_PRIKEY'); }
			if (defined('FTP_USER')) { $this->versions['FTP_USER'] = constant('FTP_USER'); }
			if (defined('FTP_PASS')) { $this->versions['FTP_PASS'] = constant('FTP_PASS'); }
			if (defined('FTP_HOST')) { $this->versions['FTP_HOST'] = constant('FTP_HOST'); }
			if (defined('FTP_SSL')) { $this->versions['FTP_SSL'] = (constant('FTP_SSL') === true ? "Yes" : "No"); }
			if (defined('PHP_OS')) { $this->versions['PHP_OS'] = constant('PHP_OS'); }

			$userAgent = $_SERVER['HTTP_USER_AGENT'];
			if (strlen($userAgent) > 15) {
				$arr = explode(" ", $userAgent);
				if (count($arr) > 0) {
					$userAgent = $arr[0];
				} else {
					$userAgent = "";
				}
			}
			if (strlen($userAgent) > 0) { $this->versions['HTTP_USER_AGENT'] = $userAgent; }
			// Demo mode code
			$userlogin = "";
			if (defined("DEMO_USER")) {
				$userlogin = strtolower(constant("DEMO_USER"));
			}
			if ($userlogin == strtolower(wp_get_current_user()->user_login) && defined("DEMO_CAPABILITY")) {
				$capability = constant("DEMO_CAPABILITY");
			} else {
				$capability = "manage_options";
			}

			$this->serverData->add(	[	'itemName'		=> 'WORDPRESS_VERSION',
										'text'			=> 'WordPress: ',
										'sequence'		=>	0,
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Versions'
									]);
			$this->serverData->add(	[	'itemName'		=> 'PHP_VERSION',
										'sequence'		=>	1,
										'text'			=> 'PHP: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Versions'
									]);
			$this->serverData->add(	[	'itemName'		=> 'MYSQL_VERSION',
										'sequence'		=>	2,
										'text'			=> 'MySQL: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Versions'
									] );
			$this->serverData->add( [	'itemName'		=> 'MYSQL_GENERAL_LOG',
										'sequence'		=> 2,
										'text'			=> 'MySQL General Log',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Logging',
									]);
			$this->serverData->add(	[	'itemName'		=>	'Install_Root',
										'sequence'		=>	5,
										'text'			=>	'WP Install Root: ',
										'data'			=>	&$this->versions,
										'initialState'	=>	'visible',
										'capability'	=>	$capability,
										'colorIfTrue'	=>	'',
										'groupName'		=>	'WordPress',
									]);
			if ($this->versions['MYSQL_GENERAL_LOG'] != "Disabled") {
				$this->serverData->add( [	'itemName'		=> 'MYSQL_GENERAL_LOG_FILE',
											'sequence'		=> 3,
											'text'			=> 'MySQL Log File',
											'data'			=> &$this->versions,
											'initialState'	=> 'visible',
											'capability'	=> $capability,
											'colorIfTrue'	=> '',
											'groupName'		=> 'Logging',
										]);
			}
			$this->serverData->add(	[	'itemName'		=> 'WP_DEBUG',
										'sequence'		=>	0,
										'text'			=> 'WP_DEBUG: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> 'red',
										'groupName'		=> 'Logging'
									]);
			$this->serverData->add(	[	'itemName'		=> 'WP_DEBUG_LOG',
										'sequence'		=>	1,
										'text'			=> 'WP_DEBUG_LOG: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> 'red',
										'groupName'		=> 'Logging'
									]);
			$this->serverData->add( [	'itemName'		=> 'SITE URL',
										'text'			=> 'Site URL: ',
										'sequence'		=>	0,
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'WordPress'
									]);
			$this->serverData->add( [	'itemName'		=> 'WP URL',
										'text'			=> 'WordPress URL: ',
										'sequence'		=>	1,
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'WordPress'
									]);
			$this->serverData->add( [	'itemName'		=> 'ADM EMAIL',
										'text'			=> 'Admin Email Address: ',
										'sequence'		=>	2,
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'WordPress'
									]);
			$this->serverData->add(	[	'itemName'		=> 'CURRENT_THEME',
										'sequence'		=>	3,
										'text'			=> 'Currect Active Theme: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'WordPress'
									]);
			$this->serverData->add(	[	'itemName'		=> 'WP_ALLOW_MULTISITE',
										'sequence'		=>	4,
										'text'			=> 'Allow MultiSite: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> 'red',
										'groupName'		=> 'WordPress'
									]);
			$this->serverData->add(	[	'itemName'		=> 'SERVER_SOFTWARE',
										'sequence'		=>	1,
										'text'			=> 'Server Software: ',
										'data'			=> &$_SERVER,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Web Server'
									]);
			$this->serverData->add(	[	'itemName'		=> 'SERVER_NAME',
										'sequence'		=>	0,
										'text'			=> 'Server Name: ',
										'data'			=> &$_SERVER,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Web Server'
									]);
			$this->serverData->add(	[	'itemName'		=> 'SERVER_ADDR',
										'sequence'		=>	2,
										'text'			=> 'Server Address: ',
										'data'			=> &$_SERVER,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Web Server'
									]);
			$this->serverData->add(	[	'itemName'		=> 'SERVER_ADMIN',
										'sequence'		=>	3,
										'text'			=> 'Server Admin: ',
										'data'			=> &$_SERVER,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Web Server'
									]);
			$this->serverData->add(	[	'itemName'		=> 'PHP_OS',
										'sequence'		=>	4,
										'text'			=> 'Server OS: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'Web Server'
								]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_BASE',
										'sequence'		=>	0,
										'text'			=> 'WordPress Root: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_CONTENT_DIR',
										'sequence'		=>	1,
										'text'			=> 'WordPress Content Folder: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_PLUGIN_DIR',
										'sequence'		=>	2,
										'text'			=> 'WordPress Plugin Folder: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> $capability,
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_PUBKEY',
										'sequence'		=>	3,
										'text'			=> 'FTP SSH Public Key Path: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_PRIKEY',
										'sequence'		=>	4,
										'text'			=> 'FTP SSH Private Key Path: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FS_METHOD',
										'sequence'		=>	5,
										'text'			=> 'FTP Connection Method',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_USER',
										'sequence'		=>	6,
										'text'			=> 'FTP Username: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_PASS',
										'sequence'		=>	7,
										'text'			=> 'FTP Password: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_HOST',
										'sequence'		=>	8,
										'text'			=> 'FTP Host: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'FTP_SSL',
										'sequence'		=>	9,
										'text'			=> 'FTP Use SSL: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'FTP'
									]);
			$this->serverData->add(	[	'itemName'		=> 'DB_HOST',
										'sequence'		=>	0,
										'text'			=> 'Database Host: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Database'
									]);
			$this->serverData->add(	[	'itemName'		=> 'DB_USER',
										'sequence'		=>	1,
										'text'			=> 'Database User: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Database'
									]);
			$this->serverData->add(	[	'itemName'		=> 'DB_PASSWORD',
										'sequence'		=>	2,
										'text'			=> 'Database Password: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Database'
									]);
			$this->serverData->add(	[	'itemName'		=> 'DB_NAME',
										'sequence'		=>	3,
										'text'			=> 'Database Name: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'hidden',
										'capability'	=> 'manage_options',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Database'
									]);
			$this->serverData->add(	[	'itemName'		=> 'REMOTE_ADDR',
										'sequence'		=>	2,
										'text'			=> 'Your IP: ',
										'data'			=> &$_SERVER,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Your Info'
									]);
			$this->serverData->add(	[	'itemName'		=> 'HTTP_USER_AGENT',
										'sequence'		=>	3,
										'text'			=> 'Your Browser: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Your Info'
									]);
			$this->serverData->add(	[	'itemName'		=> 'LOGGED-ON-USER',
										'sequence'		=>	0,
										'text'			=> 'Your Name: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Your Info'
									]);
			$this->serverData->add(	[	'itemName'		=> 'LOGGED-ON-USER-EMAIL',
										'sequence'		=>	1,
										'text'			=> 'Your Email: ',
										'data'			=> &$this->versions,
										'initialState'	=> 'visible',
										'capability'	=> 'read',
										'colorIfTrue'	=> '',
										'groupName'		=> 'Your Info'
									]);
		}
	}
}
