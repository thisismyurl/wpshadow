<?php
/**
 * Tests for Static Asset Caching Headers Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6028.1530
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Asset_Caching_Headers;
use WP_Mock\Tools\TestCase;

/**
 * Asset Caching Headers Diagnostic Test Class
 *
 * @since 1.6028.1530
 */
class AssetCachingHeadersTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes with optimal caching
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_passes_with_optimal_caching(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(),
				'short_cache'   => array(),
				'optimal_cache' => array_fill( 0, 5, array(
					'url'  => 'https://example.com/style.css',
					'type' => 'css',
				) ),
			),
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags assets without caching
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_flags_assets_without_caching(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'main-style',
						'cache_duration' => 0,
					),
					array(
						'url'            => 'https://example.com/script.js',
						'type'           => 'js',
						'handle'         => 'main-script',
						'cache_duration' => 0,
					),
				),
				'short_cache'   => array(),
				'optimal_cache' => array_fill( 0, 3, array() ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => '2 static assets have missing or insufficient cache headers',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'asset-caching-headers', $result['id'] );
		$this->assertEquals( 2, $result['meta']['no_cache_count'] );
	}

	/**
	 * Test diagnostic flags short cache durations
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_flags_short_cache_duration(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(),
				'short_cache'   => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'main-style',
						'cache_duration' => 3600, // 1 hour.
					),
				),
				'optimal_cache' => array_fill( 0, 4, array() ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => '1 static assets have missing or insufficient cache headers',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['short_cache_count'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'test',
						'cache_duration' => 0,
					),
				),
				'short_cache'   => array(),
				'optimal_cache' => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have missing cache headers',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

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

		$this->assertEquals( 'asset-caching-headers', $result['id'] );
		$this->assertEquals( 'performance', $result['family'] );
		$this->assertTrue( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes performance metrics
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_meta_includes_performance_metrics(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 10,
				'no_cache'      => array_fill( 0, 3, array(
					'url'            => 'https://example.com/test.css',
					'type'           => 'css',
					'handle'         => 'test',
					'cache_duration' => 0,
				) ),
				'short_cache'   => array_fill( 0, 2, array(
					'url'            => 'https://example.com/test.js',
					'type'           => 'js',
					'handle'         => 'test',
					'cache_duration' => 3600,
				) ),
				'optimal_cache' => array_fill( 0, 5, array() ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'checked_assets', $result['meta'] );
		$this->assertArrayHasKey( 'no_cache_count', $result['meta'] );
		$this->assertArrayHasKey( 'short_cache_count', $result['meta'] );
		$this->assertArrayHasKey( 'optimal_cache_count', $result['meta'] );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
	}

	/**
	 * Test details include htaccess solution
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_details_include_htaccess_solution(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'test',
						'cache_duration' => 0,
					),
				),
				'short_cache'   => array(),
				'optimal_cache' => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'htaccess_solution', $result['details'] );
		$this->assertStringContainsString( 'mod_expires', $result['details']['htaccess_solution'] );
	}

	/**
	 * Test details include recommended cache times
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_details_include_recommended_cache_times(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'test',
						'cache_duration' => 0,
					),
				),
				'short_cache'   => array(),
				'optimal_cache' => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommended_cache_times', $result['details'] );
		$this->assertArrayHasKey( 'css', $result['details']['recommended_cache_times'] );
		$this->assertArrayHasKey( 'js', $result['details']['recommended_cache_times'] );
	}

	/**
	 * Test details include alternative solutions
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_details_include_alternative_solutions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'test',
						'cache_duration' => 0,
					),
				),
				'short_cache'   => array(),
				'optimal_cache' => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'alternative_solutions', $result['details'] );
		$this->assertNotEmpty( $result['details']['alternative_solutions'] );
	}

	/**
	 * Test threat level increases with more issues
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_threat_level_increases_with_issues(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 10,
				'no_cache'      => array_fill( 0, 8, array(
					'url'            => 'https://example.com/test.css',
					'type'           => 'css',
					'handle'         => 'test',
					'cache_duration' => 0,
				) ),
				'short_cache'   => array(),
				'optimal_cache' => array_fill( 0, 2, array() ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 45, $result['threat_level'] );
	}

	/**
	 * Test performance impact calculation
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_performance_impact_calculation(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 10,
				'no_cache'      => array_fill( 0, 8, array(
					'url'            => 'https://example.com/test.css',
					'type'           => 'css',
					'handle'         => 'test',
					'cache_duration' => 0,
				) ),
				'short_cache'   => array(),
				'optimal_cache' => array_fill( 0, 2, array() ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
		$this->assertStringContainsString( 'impact', strtolower( $result['meta']['performance_impact'] ) );
	}

	/**
	 * Test details include problematic assets
	 *
	 * @since 1.6028.1530
	 * @return void
	 */
	public function test_details_include_problematic_assets(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'checked_count' => 5,
				'no_cache'      => array(
					array(
						'url'            => 'https://example.com/style.css',
						'type'           => 'css',
						'handle'         => 'test',
						'cache_duration' => 0,
					),
				),
				'short_cache'   => array(),
				'optimal_cache' => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Assets have issues',
		) );

		$result = Diagnostic_Asset_Caching_Headers::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'problematic_assets', $result['details'] );
		$this->assertNotEmpty( $result['details']['problematic_assets'] );
	}
}
