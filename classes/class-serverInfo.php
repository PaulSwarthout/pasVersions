<?PHP
if (! class_exists('serverInfo') ) {
	class serverInfo {
		private	$serverAttributes;
		private $groupings;

		function __construct() {
			$this->serverAttributes = get_option("pas_wbe_attributes", []);
			$groupings				= get_option("pas_wbe_groupings", []);
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
			return ($a['groupValue'] . "_" . $a['sequence'] <= $b['groupValue'] . "_" . $b['sequence'] ? -1 : 1);
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
