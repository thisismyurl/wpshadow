<?php
/**
 * Class SampleTest
 *
 * @package WPShadow
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * Test that the plugin is loaded.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'WPSHADOW_VERSION' ) );
	}

	/**
	 * Test basic WordPress functionality.
	 */
	public function test_wordpress_loaded() {
		$this->assertTrue( function_exists( 'do_action' ) );
		$this->assertTrue( function_exists( 'add_filter' ) );
	}

	/**
	 * Test plugin structure.
	 */
	public function test_plugin_structure() {
		$plugin_file = dirname( __DIR__ ) . '/wpshadow.php';
		$this->assertFileExists( $plugin_file );
	}
}
