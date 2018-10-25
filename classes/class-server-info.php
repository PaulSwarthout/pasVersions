<?PHP
if (! class_exists('pas_wse_server_info') ) {
	class pas_wse_server_info {
		private	$serverAttributes;
		private $groupings;
		private $library;
		private $plugin_directory;

		function __construct($args) {
			$this->serverAttributes = get_option("pas_wse_attributes", []);
			$groupings				= get_option("pas_wse_groupings", []);

			$this->library			= $args['libraryFunctions'];
			$this->plugin_directory = $args['plugin_directory'];

			if (count($groupings) > 0) {
				setGroupings($groupings);
			}
		}
		function setGroupings($args) {
			foreach ($args as $key => $value) {
				$this->groupings[$value] = $key;
			}
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
		function comparison($a, $b) {
			return ($a['groupValue'] . "_" . $this->library->digits($a['sequence'], 2) <= $b['groupValue'] . "_" . $this->library->digits($b['sequence'], 2) ? -1 : 1);
		}
		function getAttributesSortedByGroup() {
			foreach ($this->serverAttributes as $key => $attribute) {
				$this->serverAttributes[$key]['groupValue'] =
					$this->groupings[$attribute['groupName']];
			}
			uasort($this->serverAttributes, Array($this, 'comparison'));
			return $this->serverAttributes;
		}
		function getAttributesUnsorted() {
			return $this->serverAttributes;
		}
	}
}
