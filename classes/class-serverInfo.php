<?PHP
if (! class_exists('serverInfo') ) {
	class serverInfo {
		public $serverAttributes;

		function __construct() {
			$serverAttributes = [];
		}

		function add($args) {
			$this->serverAttributes[$args['itemName']] = $args;
		}
		function get($itemName) {
			if (array_key_exists($itemName, $this->serverAttributes)) {
				return $this->serverAttributes[$itemName];
			} else {
				return null;
			}
		}
	}
}
