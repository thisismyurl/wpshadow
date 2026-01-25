<?php
/**
 * Sample test to verify setup
 */

class Test_Sample extends WP_UnitTestCase {
	public function test_wordpress_loaded() {
		$this->assertTrue( function_exists( 'do_action' ) );
	}

	public function test_plugin_loaded() {
		$this->assertTrue( class_exists( 'WPShadow\\Core\\Plugin' ) );
	}
}
