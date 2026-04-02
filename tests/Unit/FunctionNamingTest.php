<?php
/**
 * Tests for Function Naming Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Function_Naming;
use WP_Mock\Tools\TestCase;

/**
 * Function Naming Test Class
 *
 * @since 1.6093.1200
 */
class FunctionNamingTest extends TestCase {

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
	 * Test diagnostic passes with compliant functions
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_compliant_functions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_Function_Naming::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags camelCase functions
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_camelcase_functions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 100,
				'total_violations'      => 15,
				'compliance_percentage' => 85.0,
				'violations'            => array(
					array( 'function' => 'myFunction', 'violation_type' => 'camelCase' ),
				),
				'violations_by_type'    => array( 'camelCase' => 15 ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['meta']['total_violations'] );
	}

	/**
	 * Test diagnostic flags PascalCase functions
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_pascalcase_functions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 50,
				'total_violations'      => 10,
				'compliance_percentage' => 80.0,
				'violations'            => array(
					array( 'function' => 'MyFunction', 'violation_type' => 'PascalCase' ),
				),
				'violations_by_type'    => array( 'PascalCase' => 10 ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'PascalCase', $result['details']['violations_by_type'] );
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
				'total_functions'       => 100,
				'total_violations'      => 5,
				'compliance_percentage' => 95.0,
				'violations'            => array(),
				'violations_by_type'    => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

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

		$this->assertEquals( 'function-naming-convention', $result['id'] );
		$this->assertEquals( 'code-quality', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes compliance percentage
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_compliance_percentage(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 200,
				'total_violations'      => 20,
				'compliance_percentage' => 90.0,
				'violations'            => array(),
				'violations_by_type'    => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'compliance_percentage', $result['meta'] );
		$this->assertEquals( 90.0, $result['meta']['compliance_percentage'] );
	}

	/**
	 * Test details include violations list
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_violations_list(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 100,
				'total_violations'      => 10,
				'compliance_percentage' => 90.0,
				'violations'            => array(
					array( 'function' => 'badFunction', 'line' => 10, 'suggested_name' => 'bad_function' ),
					array( 'function' => 'AnotherBad', 'line' => 20, 'suggested_name' => 'another_bad' ),
				),
				'violations_by_type'    => array( 'camelCase' => 1, 'PascalCase' => 1 ),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'violations', $result['details'] );
		$this->assertCount( 2, $result['details']['violations'] );
	}

	/**
	 * Test recommendations included
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_recommendations_included(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 50,
				'total_violations'      => 5,
				'compliance_percentage' => 90.0,
				'violations'            => array(),
				'violations_by_type'    => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'recommendations', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommendations'] );
	}

	/**
	 * Test severity scales with compliance
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_severity_scales_with_compliance(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 100,
				'total_violations'      => 5,
				'compliance_percentage' => 95.0,
				'violations'            => array(),
				'violations_by_type'    => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'low', $result['severity'] );
		$this->assertEquals( 10, $result['threat_level'] );
	}

	/**
	 * Test high severity with low compliance
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_high_severity_with_low_compliance(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 100,
				'total_violations'      => 50,
				'compliance_percentage' => 50.0,
				'violations'            => array(),
				'violations_by_type'    => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 20, $result['threat_level'] );
	}

	/**
	 * Test violations grouped by type
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_violations_grouped_by_type(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_functions'       => 100,
				'total_violations'      => 15,
				'compliance_percentage' => 85.0,
				'violations'            => array(),
				'violations_by_type'    => array(
					'camelCase'  => 10,
					'PascalCase' => 5,
				),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Function_Naming::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'violations_by_type', $result['details'] );
		$this->assertEquals( 10, $result['details']['violations_by_type']['camelCase'] );
		$this->assertEquals( 5, $result['details']['violations_by_type']['PascalCase'] );
	}
}
