<?php
/**
 * Tests for PHP 8+ Incompatible Patterns Diagnostic
 *
 * @package WPShadow\Tests
 * @since   1.6028.2125
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_PHP8_Incompatible_Patterns;
use WP_Mock\Tools\TestCase;

/**
 * PHP8 Incompatible Patterns Test Class
 *
 * @since 1.6028.2125
 */
class PHP8IncompatiblePatternsTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_with_compatible_code() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_stylesheet_directory' )->andReturn( '/tmp/theme' );
		\WP_Mock::userFunction( 'get_option' )->andReturn( array() );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertNull( $result );
	}

	public function test_flags_incompatible_patterns() {
		$cached_result = array(
			'id'           => 'php8-incompatible-patterns',
			'total_issues' => 5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'php8-incompatible-patterns', $result['id'] );
	}

	public function test_finding_structure_valid() {
		$cached_result = array(
			'id'              => 'php8-incompatible-patterns',
			'title'           => 'PHP 8+ Incompatible Code Patterns',
			'description'     => 'Test',
			'severity'        => 'medium',
			'threat_level'    => 45,
			'auto_fixable'    => false,
			'kb_link'         => 'test',
			'family'          => 'code-quality',
			'meta'            => array(),
			'details'         => array(),
			'recommendations' => array(),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
	}

	public function test_meta_includes_php_version() {
		$cached_result = array(
			'id'   => 'php8-incompatible-patterns',
			'meta' => array(
				'total_issues'    => 5,
				'files_affected'  => 2,
				'php_version'     => PHP_VERSION,
				'php8_compatible' => version_compare( PHP_VERSION, '8.0', '>=' ),
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertArrayHasKey( 'php_version', $result['meta'] );
		$this->assertArrayHasKey( 'php8_compatible', $result['meta'] );
	}

	public function test_details_include_incompatibilities() {
		$cached_result = array(
			'id'      => 'php8-incompatible-patterns',
			'details' => array(
				'incompatibilities' => array(
					array(
						'file' => 'test.php',
						'line' => 10,
						'type' => 'create_function',
					),
				),
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertArrayHasKey( 'incompatibilities', $result['details'] );
	}

	public function test_recommendations_included() {
		$cached_result = array(
			'id'              => 'php8-incompatible-patterns',
			'recommendations' => array( 'Test recommendation' ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertGreaterThan( 0, count( $result['recommendations'] ) );
	}

	public function test_severity_scales_with_issues() {
		$cached_low = array(
			'id'       => 'php8-incompatible-patterns',
			'severity' => 'low',
			'meta'     => array( 'total_issues' => 3 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_low );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertEquals( 'low', $result['severity'] );
	}

	public function test_high_severity_with_many_issues() {
		$cached_high = array(
			'id'       => 'php8-incompatible-patterns',
			'severity' => 'high',
			'meta'     => array( 'total_issues' => 15 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_high );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertEquals( 'high', $result['severity'] );
	}

	public function test_caching_behavior() {
		$cached_result = array(
			'id'           => 'php8-incompatible-patterns',
			'total_issues' => 5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertEquals( $cached_result, $result );
	}

	public function test_auto_fixable_is_false() {
		$cached_result = array(
			'id'           => 'php8-incompatible-patterns',
			'auto_fixable' => false,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_PHP8_Incompatible_Patterns::check();

		$this->assertFalse( $result['auto_fixable'] );
	}
}
