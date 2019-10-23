<?php

namespace wpsolr\pro\extensions;

use wpsolr\core\classes\WPSOLR_UnitTestCase;

/**
 * Class WPSOLR_Extensions_Test_Abstract
 * @package wpsolr\pro\extensions
 */
abstract class WPSOLR_Extensions_Test_Abstract extends WPSOLR_UnitTestCase {


	/**
	 * Load the admin page of all the extensions by adding this test.
	 */
	function test_load_admin_page() {
		ob_start();
		// Get path of the inherited class, not of the current parent class.
		include dirname( ( new \ReflectionClass( $this ) )->getFileName() ) . '/admin_options.inc.php';
		ob_end_clean();
	}

}


