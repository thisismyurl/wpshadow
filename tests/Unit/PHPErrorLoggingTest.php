<?php
/**
 * Tests for PHP Error Logging Status Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_PHP_Error_Logging;
use WP_Mock\Tools\TestCase;

/**
 * PHP Error Logging Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class PHPErrorLoggingTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes when properly configured
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_when_properly_configured(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issues' => array(),
			),
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags debug display on production
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_debug_display_on_production(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => true,
				'wp_debug_display'      => true,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => true,
				'issues'                => array( 'WP_DEBUG_DISPLAY is enabled on production' ),
				'config_issues'         => array( 'debug_display_production' ),
				'debug_log_exists'      => true,
				'debug_log_size'        => 1024,
				'recent_errors'         => 5,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '1KB',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'php-error-logging', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertTrue( $result['meta']['has_critical_issues'] );
	}

	/**
	 * Test diagnostic flags debug without logging on production
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_debug_without_logging_production(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => false,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => false,
				'issues'                => array( 'WP_DEBUG enabled without WP_DEBUG_LOG' ),
				'config_issues'         => array( 'debug_without_logging' ),
				'debug_log_exists'      => false,
				'debug_log_size'        => 0,
				'recent_errors'         => 0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '0B',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertContains( 'debug_without_logging', $result['meta']['config_issues'] );
	}

	/**
	 * Test diagnostic suggests enabling debug on development
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_suggests_enabling_debug_on_development(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => false,
				'wp_debug_log'          => false,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => false,
				'has_critical_issues'   => false,
				'issues'                => array( 'WP_DEBUG is disabled on development environment' ),
				'config_issues'         => array( 'debug_disabled_dev' ),
				'debug_log_exists'      => false,
				'debug_log_size'        => 0,
				'recent_errors'         => 0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '0B',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertFalse( $result['meta']['is_production'] );
		$this->assertContains( 'debug_disabled_dev', $result['meta']['config_issues'] );
	}

	/**
	 * Test diagnostic flags oversized debug log
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_oversized_debug_log(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => true,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => false,
				'issues'                => array( 'debug.log file is very large (6.5MB)' ),
				'config_issues'         => array( 'oversized_debug_log' ),
				'debug_log_exists'      => true,
				'debug_log_size'        => 6815744, // > 5MB.
				'recent_errors'         => 50,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issue',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => function( $single, $plural, $count ) {
				return $count > 1 ? $plural : $single;
			},
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '6.5MB',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertContains( 'oversized_debug_log', $result['meta']['config_issues'] );
		$this->assertEquals( '6.5MB', $result['meta']['debug_log_size_human'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => false,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => false,
				'issues'                => array( 'Test issue' ),
				'config_issues'         => array( 'test' ),
				'debug_log_exists'      => false,
				'debug_log_size'        => 0,
				'recent_errors'         => 0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '0B',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertArrayHasKey( 'family', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );

		$this->assertEquals( 'php-error-logging', $result['id'] );
		$this->assertEquals( 'code-quality', $result['family'] );
		$this->assertTrue( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes debug configuration
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_debug_config(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => true,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => false,
				'issues'                => array( 'Test' ),
				'config_issues'         => array( 'test' ),
				'debug_log_exists'      => true,
				'debug_log_size'        => 1024,
				'recent_errors'         => 10,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '1KB',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertTrue( $result['meta']['wp_debug'] );
		$this->assertTrue( $result['meta']['wp_debug_log'] );
		$this->assertFalse( $result['meta']['wp_debug_display'] );
		$this->assertTrue( $result['meta']['debug_log_exists'] );
		$this->assertEquals( 10, $result['meta']['recent_errors'] );
	}

	/**
	 * Test details include current configuration
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_current_config(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => true,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => false,
				'issues'                => array( 'Test' ),
				'config_issues'         => array( 'test' ),
				'debug_log_exists'      => false,
				'debug_log_size'        => 0,
				'recent_errors'         => 0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '0B',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'current_config', $result['details'] );
		$this->assertArrayHasKey( 'WP_DEBUG', $result['details']['current_config'] );
	}

	/**
	 * Test details include recommended configuration
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_recommended_config(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => false,
				'wp_debug_display'      => true,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => true,
				'issues'                => array( 'Test' ),
				'config_issues'         => array( 'test' ),
				'debug_log_exists'      => false,
				'debug_log_size'        => 0,
				'recent_errors'         => 0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '0B',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommended_config', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommended_config'] );
	}

	/**
	 * Test details include why this matters
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_why_matters(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => false,
				'wp_debug_log'          => false,
				'wp_debug_display'      => false,
				'script_debug'          => false,
				'is_production'         => false,
				'has_critical_issues'   => false,
				'issues'                => array( 'Test' ),
				'config_issues'         => array( 'test' ),
				'debug_log_exists'      => false,
				'debug_log_size'        => 0,
				'recent_errors'         => 0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '0B',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'why_this_matters', $result['details'] );
		$this->assertNotEmpty( $result['details']['why_this_matters'] );
	}

	/**
	 * Test threat level increases with critical issues
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_threat_level_increases_with_critical_issues(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'wp_debug'              => true,
				'wp_debug_log'          => true,
				'wp_debug_display'      => true,
				'script_debug'          => false,
				'is_production'         => true,
				'has_critical_issues'   => true,
				'issues'                => array( 'Critical issue' ),
				'config_issues'         => array( 'debug_display_production' ),
				'debug_log_exists'      => true,
				'debug_log_size'        => 6000000,
				'recent_errors'         => 150,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '5.7MB',
		) );

		$result = Diagnostic_PHP_Error_Logging::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 60, $result['threat_level'] );
		$this->assertLessThanOrEqual( 75, $result['threat_level'] );
	}
}
