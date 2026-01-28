<?php
/**
 * Tests for Database Query Performance Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6027.1500
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Query_Performance;
use WP_Mock\Tools\TestCase;

/**
 * Database Query Performance Diagnostic Test Class
 *
 * @since 1.6027.1500
 */
class QueryPerformanceTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		// Define WordPress constants.
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic returns null when no slow queries
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_passes_when_no_slow_queries(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 10,
				'total_time'   => 0.05,
				'average_time' => 0.005,
				'slow_queries' => array(),
			),
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags slow queries
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_flags_slow_queries(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts WHERE post_status = "publish"',
						'time'         => 0.35,
						'stack'        => 'wp_query->get_posts()',
						'optimization' => 'Add index on post_status',
					),
					array(
						'sql'          => 'SELECT * FROM wp_options WHERE option_name LIKE "%transient%"',
						'time'         => 0.25,
						'stack'        => 'get_option()',
						'optimization' => 'Use full-text search',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 2 slow database queries exceeding 0.1 seconds',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'query-performance', $result['id'] );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 2, $result['meta']['slow_query_count'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 0.35,
						'stack'        => 'test',
						'optimization' => 'Test optimization',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 slow database queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

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

		$this->assertEquals( 'query-performance', $result['id'] );
		$this->assertEquals( 'performance', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test finding includes top slow queries
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_finding_includes_top_slow_queries(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 0.35,
						'stack'        => 'test',
						'optimization' => 'Add index',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 slow database queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'top_slow_queries', $result['details'] );
		$this->assertNotEmpty( $result['details']['top_slow_queries'] );

		$first_query = $result['details']['top_slow_queries'][0];
		$this->assertArrayHasKey( 'rank', $first_query );
		$this->assertArrayHasKey( 'time', $first_query );
		$this->assertArrayHasKey( 'sql', $first_query );
		$this->assertArrayHasKey( 'optimization', $first_query );
	}

	/**
	 * Test finding includes optimization tips
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_finding_includes_optimization_tips(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 0.35,
						'stack'        => 'test',
						'optimization' => 'Test',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'optimization_tips', $result['details'] );
		$this->assertNotEmpty( $result['details']['optimization_tips'] );
	}

	/**
	 * Test threat level increases with more slow queries
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_threat_level_increases_with_slow_query_count(): void {
		// Test with many slow queries.
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 100,
				'total_time'   => 10.0,
				'average_time' => 0.1,
				'slow_queries' => array_fill( 0, 15, array(
					'sql'          => 'SELECT * FROM wp_posts',
					'time'         => 0.2,
					'stack'        => 'test',
					'optimization' => 'Test',
				) ),
				'slowest_time' => 0.5,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 60, $result['threat_level'] );
	}

	/**
	 * Test threat level increases with slower query times
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_threat_level_increases_with_query_time(): void {
		// Test with very slow query.
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 5.0,
				'average_time' => 0.1,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 2.5,
						'stack'        => 'test',
						'optimization' => 'Test',
					),
				),
				'slowest_time' => 2.5,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 55, $result['threat_level'] );
	}

	/**
	 * Test meta includes performance metrics
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_meta_includes_performance_metrics(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 0.35,
						'stack'        => 'test',
						'optimization' => 'Test',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'total_queries', $result['meta'] );
		$this->assertArrayHasKey( 'slow_query_count', $result['meta'] );
		$this->assertArrayHasKey( 'total_query_time', $result['meta'] );
		$this->assertArrayHasKey( 'average_query_time', $result['meta'] );
		$this->assertArrayHasKey( 'slowest_query_time', $result['meta'] );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
	}

	/**
	 * Test finding includes immediate actions
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_finding_includes_immediate_actions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 0.35,
						'stack'        => 'test',
						'optimization' => 'Test',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'immediate_actions', $result['details'] );
		$this->assertNotEmpty( $result['details']['immediate_actions'] );
	}

	/**
	 * Test finding includes advanced options
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_finding_includes_advanced_options(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 50,
				'total_time'   => 2.5,
				'average_time' => 0.05,
				'slow_queries' => array(
					array(
						'sql'          => 'SELECT * FROM wp_posts',
						'time'         => 0.35,
						'stack'        => 'test',
						'optimization' => 'Test',
					),
				),
				'slowest_time' => 0.35,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'advanced_options', $result['details'] );
		$this->assertNotEmpty( $result['details']['advanced_options'] );
	}

	/**
	 * Test severity is high for many slow queries
	 *
	 * @since 1.6027.1500
	 * @return void
	 */
	public function test_severity_high_for_many_slow_queries(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'query_count'  => 100,
				'total_time'   => 10.0,
				'average_time' => 0.1,
				'slow_queries' => array_fill( 0, 20, array(
					'sql'          => 'SELECT * FROM wp_posts',
					'time'         => 0.5,
					'stack'        => 'test',
					'optimization' => 'Test',
				) ),
				'slowest_time' => 1.5,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found slow queries',
		) );

		\WP_Mock::userFunction( 'number_format', array(
			'return_arg' => 0,
		) );

		$result = Diagnostic_Query_Performance::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertGreaterThan( 60, $result['threat_level'] );
	}
}
