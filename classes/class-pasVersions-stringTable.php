<?php
if (! class_exists('pasVersions_StringTable') ) {
	class pasVersions_StringTable {
		var $stringArray;

		function __construct() {
			$this->stringArray = Array();
			$this->stringArray['THANK YOU FOR INSTALLING'] = <<<'THANKYOU'
				Thank you for installing the Development Environment Plugin.<br><br>
				
				This plugin was designed to display as a widget on the dashboard.&nbsp;
				Among the many pieces of information that it displays, is the database password and the FTP password.&nbsp;
				For most WordPress installations, this wouldn't be a problem.&nbsp;
				Unfortunately, as luck would have it, I'm developing another WordPress plugin which will give people.&nbsp;
				who lack 'manage_options' capabilities access to the dashboard in a somewhat limited capacity as set forth
				by other custom roles and capabilities.&nbsp;Honestly, I never really considered this problem.&nbsp;
				But now that I have, I suspect that at least a few Wordpress Developers may encounter this problem as well.&nbsp;
				<br>
				Therefore, before the Development Environment plugin is enabled, and made to reveal it's information, the WordPress Developer
				needs to choose how the Development Environment plugin displays it's data.&nbsp;
				This selection can be changed at any time in the future by any WordPress user who has been assigned the capability "manage_options".&nbsp;
THANKYOU;
			$this->stringArray['HIDE_MENU_OPTION'] = <<<'HIDEMENU'
				<p>
					Normally, WordPress plugins have a menu entry on the dashboard menu.
					If the Development Environment plugin is configured to display it's information as a Dashboard Widget, then the Dashboard Menu entry is not required.&nbsp;
					If you have chosen to display the Development Environment plugin's information as a Dashboard Widget, then you can check the box below to hide the Dashboard Menu entry.&nbsp;
					We understand how cluttered the Dashboard Menu can get when you have many plugins installed.&nbsp;
					As such, we are giving you the opportunity to have one less.&nbsp;
					If you disable the Dashboard Menu entry, you can click the "reveal menu" link in the lower right corner of the Dashboard Widget to re-enable the Dashboard Menu entry.<br><br>
				</p>
HIDEMENU;
		}
		function getString($byName) {
			if (array_key_exists($byName, $this->stringArray)) {
				return $this->stringArray[$byName];
			} else {
				return null;
			}
		}
	}
}
