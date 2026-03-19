<?php
/**
 * Tests for Plugin/Theme Active Count Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Active_Count;
use WP_Mock\Tools\TestCase;

/**
 * Active Count Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class ActiveCountTest extends TestCase {

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
	 * Test diagnostic passes with healthy plugin count
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_healthy_count(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 15,
				'must_use_plugins'  => 2,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 17,
				'inactive_plugins'  => 3,
				'theme_name'        => 'Twenty Twenty-Four',
				'active_theme_count' => 1,
				'issues'            => array(),
			),
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags excessive plugins
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_excessive_plugins(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 55,
				'must_use_plugins'  => 3,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 58,
				'inactive_plugins'  => 12,
				'theme_name'        => 'Astra',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array(
					'Site has 58 active plugins - excessive count',
					'12 inactive plugins detected',
				),
				'recommendations'   => array( 'Review plugins' ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Site has 58 active plugins. Found 2 issues.',
		) );

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '1MB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'plugin-theme-active-count', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 58, $result['meta']['total_plugins'] );
	}

	/**
	 * Test diagnostic flags above recommended
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_above_recommended(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 35,
				'must_use_plugins'  => 2,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 37,
				'inactive_plugins'  => 5,
				'theme_name'        => 'GeneratePress',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( 'Site has 37 active plugins - above recommended' ),
				'recommendations'   => array( 'Audit plugins' ),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '500KB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertEquals( 37, $result['meta']['total_plugins'] );
	}

	/**
	 * Test diagnostic flags inactive plugins
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_inactive_plugins(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 20,
				'must_use_plugins'  => 0,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 20,
				'inactive_plugins'  => 15,
				'theme_name'        => 'Kadence',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( '15 inactive plugins detected - security risk' ),
				'recommendations'   => array( 'Delete inactive plugins' ),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '200KB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['meta']['inactive_plugins'] );
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
				'active_plugins'    => 52,
				'must_use_plugins'  => 1,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 53,
				'inactive_plugins'  => 8,
				'theme_name'        => 'Neve',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( 'Test issue' ),
				'recommendations'   => array( 'Test rec' ),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '1MB',
		) );

		$result = Diagnostic_Active_Count::check();

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

		$this->assertEquals( 'plugin-theme-active-count', $result['id'] );
		$this->assertEquals( 'settings', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes plugin counts
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_counts(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 25,
				'must_use_plugins'  => 3,
				'drop_in_plugins'   => 1,
				'total_plugins'     => 29,
				'inactive_plugins'  => 7,
				'theme_name'        => 'Hello Elementor',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( 'Test' ),
				'recommendations'   => array(),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '500KB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 25, $result['meta']['active_plugins'] );
		$this->assertEquals( 3, $result['meta']['must_use_plugins'] );
		$this->assertEquals( 1, $result['meta']['drop_in_plugins'] );
		$this->assertEquals( 29, $result['meta']['total_plugins'] );
	}

	/**
	 * Test details include recommendations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_recommendations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 40,
				'must_use_plugins'  => 0,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 40,
				'inactive_plugins'  => 2,
				'theme_name'        => 'OceanWP',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( 'Test' ),
				'recommendations'   => array( 'Audit plugins', 'Delete unused' ),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '300KB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommendations', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommendations'] );
	}

	/**
	 * Test threat level calculation
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_threat_level_calculation(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 75,
				'must_use_plugins'  => 0,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 75,
				'inactive_plugins'  => 20,
				'theme_name'        => 'Divi',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( 'Excessive plugins' ),
				'recommendations'   => array(),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '2MB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 50, $result['threat_level'] );
		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test performance impact included
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_performance_impact_included(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'active_plugins'    => 60,
				'must_use_plugins'  => 0,
				'drop_in_plugins'   => 0,
				'total_plugins'     => 60,
				'inactive_plugins'  => 5,
				'theme_name'        => 'Avada',
				'active_theme_count' => 1,
				'plugin_list'       => array(),
				'heaviest_plugins'  => array(),
				'issues'            => array( 'Too many plugins' ),
				'recommendations'   => array(),
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

		\WP_Mock::userFunction( 'size_format', array(
			'return' => '1.5MB',
		) );

		$result = Diagnostic_Active_Count::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
		$this->assertStringContainsString( 'performance', strtolower( $result['meta']['performance_impact'] ) );
	}
}
