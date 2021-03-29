<?PHP
namespace website_summary;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	function digits($v, $n) {
		return str_pad($v, $n, '0', STR_PAD_LEFT);
	}

}