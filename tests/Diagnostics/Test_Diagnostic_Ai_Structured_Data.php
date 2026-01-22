<?php
/**
 * Tests for Diagnostic_Ai_Structured_Data
 *
 * @package WPShadow
 */

namespace WPShadow\Tests\Diagnostics;

use PHPUnit\Framework\TestCase;
use WP_Mock;
use WPShadow\Diagnostics\Diagnostic_Ai_Structured_Data;

/**
 * Test case for AI Structured Data diagnostic
 */
class Test_Diagnostic_Ai_Structured_Data extends TestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	/**
	 * Test that diagnostic returns no finding when schema plugin is active
	 */
	public function test_returns_no_finding_when_schema_plugin_active() {
		// Mock get_option to return active plugins with schema plugin
		WP_Mock::userFunction( 'get_option' )
			->with( 'active_plugins', array() )
			->andReturn( array( 'wordpress-seo/wp-seo.php' ) );

		// Mock has_action to return false (not needed since plugin is active)
		WP_Mock::userFunction( 'has_action' )
			->andReturn( false );

		// Mock ABSPATH constant
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/wordpress/' );
		}

		$result = Diagnostic_Ai_Structured_Data::run();

		$this->assertIsArray( $result );
		$this->assertEmpty( $result, 'Should return empty array when schema plugin is active' );
	}

	/**
	 * Test that diagnostic returns finding when no schema is detected
	 */
	public function test_returns_finding_when_no_schema_detected() {
		// Mock get_option to return empty array (no active plugins)
		WP_Mock::userFunction( 'get_option' )
			->with( 'active_plugins', array() )
			->andReturn( array() );

		// Mock has_action to return false (no schema in theme)
		WP_Mock::userFunction( 'has_action' )
			->with( 'wp_head', 'wp_print_schema_org' )
			->andReturn( false );

		// Mock ABSPATH constant
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/wordpress/' );
		}

		$result = Diagnostic_Ai_Structured_Data::run();

		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result, 'Should return finding when no schema is detected' );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertEquals( 'ai-structured-data', $result['id'] );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'category', $result );
	}

	/**
	 * Test that check() method returns null when no issues
	 */
	public function test_check_returns_null_when_healthy() {
		// Mock get_option to return active plugins with schema plugin
		WP_Mock::userFunction( 'get_option' )
			->with( 'active_plugins', array() )
			->andReturn( array( 'schema/schema.php' ) );

		// Mock has_action
		WP_Mock::userFunction( 'has_action' )
			->andReturn( false );

		// Mock ABSPATH constant
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/wordpress/' );
		}

		$result = Diagnostic_Ai_Structured_Data::check();

		$this->assertNull( $result, 'Should return null when no issues are found' );
	}

	/**
	 * Test that check() method returns finding when issues exist
	 */
	public function test_check_returns_finding_when_issues_exist() {
		// Mock get_option to return empty array (no active plugins)
		WP_Mock::userFunction( 'get_option' )
			->with( 'active_plugins', array() )
			->andReturn( array() );

		// Mock has_action to return false
		WP_Mock::userFunction( 'has_action' )
			->with( 'wp_head', 'wp_print_schema_org' )
			->andReturn( false );

		// Mock ABSPATH constant
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/wordpress/' );
		}

		$result = Diagnostic_Ai_Structured_Data::check();

		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
		$this->assertEquals( 'ai-structured-data', $result['id'] );
	}
}
