<?PHP
class pas_wse_common_functions {
	function __construct($args = []) {
	}
	function getConstant($c, $returnIfNotDefined = false) {
		if (defined($c)) {
			return constant($c);
		} else {
			return $returnIfNotDefined;
		}
	}
}