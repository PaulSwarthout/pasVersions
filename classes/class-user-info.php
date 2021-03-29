<?PHP
namespace website_summary;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (! class_exists('pas_wse_user_info') ) {
	class pas_wse_user_info {
		private $pluginDirectory;
		private $currentUser;

		function __construct($args = []) {
			$this->pluginDirectory = (array_key_exists('pluginDirectory', $args) ? $args['pluginDirectory'] : ['url' => '', 'path' => ''] );
			$this->currentUser = wp_get_current_user();
			if ( ! $this->currentUser->exists() ) {
				unset($this->currentUser);
				$this->currentUser = null;
			}
		}

		function login() {
			return ($this->currentUser != null ? $this->currentUser->user_login : '');
		}
		function email() {
			return ($this->currentUser != null ? $this->currentUser->user_email : '');
		}
		function name() {
			return ($this->currentUser != null ? $this->currentUser->display_name : '');
		}
	}
}