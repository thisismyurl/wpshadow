<?php
/**
 * Tests for Coding Standards Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Coding_Standards;
use WP_Mock\Tools\TestCase;

/**
 * Coding Standards Test Class
 *
 * @since 1.6093.1200
 */
class CodingStandardsTest extends TestCase {

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
	 * Test diagnostic passes with compliant code
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_compliant_code(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags spacing violations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_spacing_violations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'        => 15,
				'total_lines'             => 1000,
				'compliance_percentage'   => 98.5,
				'violations_by_type'      => array( 'spacing_operators' => 10, 'spacing_keywords' => 5 ),
				'files_with_violations'   => array(
					array( 'file' => 'theme/functions.php', 'violations' => 15 ),
				),
				'total_files_scanned'     => 10,
				'total_files_with_issues' => 1,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['meta']['total_violations'] );
	}

	/**
	 * Test diagnostic flags Yoda condition violations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_yoda_condition_violations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'        => 8,
				'total_lines'             => 500,
				'compliance_percentage'   => 98.4,
				'violations_by_type'      => array( 'yoda_condition' => 8 ),
				'files_with_violations'   => array(
					array( 'file' => 'plugin/admin.php', 'violations' => 8 ),
				),
				'total_files_scanned'     => 5,
				'total_files_with_issues' => 1,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'yoda_condition', $result['details']['violations_by_type'] );
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
				'total_violations'        => 20,
				'total_lines'             => 1000,
				'compliance_percentage'   => 98.0,
				'violations_by_type'      => array( 'spacing' => 20 ),
				'files_with_violations'   => array(),
				'total_files_scanned'     => 10,
				'total_files_with_issues' => 3,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

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

		$this->assertEquals( 'coding-standards-compliance', $result['id'] );
		$this->assertEquals( 'code-quality', $result['family'] );
		$this->assertTrue( $result['auto_fixable'] );
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
				'total_violations'        => 50,
				'total_lines'             => 2000,
				'compliance_percentage'   => 97.5,
				'violations_by_type'      => array(),
				'files_with_violations'   => array(),
				'total_files_scanned'     => 20,
				'total_files_with_issues' => 5,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'compliance_percentage', $result['meta'] );
		$this->assertEquals( 97.5, $result['meta']['compliance_percentage'] );
	}

	/**
	 * Test details include top violations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_top_violations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'        => 30,
				'total_lines'             => 1500,
				'compliance_percentage'   => 98.0,
				'violations_by_type'      => array(
					'spacing_operators' => 15,
					'yoda_condition'    => 10,
					'indentation_tabs'  => 5,
				),
				'files_with_violations'   => array(
					array( 'file' => 'file1.php', 'violations' => 15 ),
					array( 'file' => 'file2.php', 'violations' => 10 ),
				),
				'total_files_scanned'     => 10,
				'total_files_with_issues' => 2,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'violations_by_type', $result['details'] );
		$this->assertCount( 3, $result['details']['violations_by_type'] );
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
				'total_violations'        => 10,
				'total_lines'             => 1000,
				'compliance_percentage'   => 99.0,
				'violations_by_type'      => array(),
				'files_with_violations'   => array(),
				'total_files_scanned'     => 5,
				'total_files_with_issues' => 1,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

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
		// Test low severity (high compliance)
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'        => 5,
				'total_lines'             => 1000,
				'compliance_percentage'   => 99.5,
				'violations_by_type'      => array(),
				'files_with_violations'   => array(),
				'total_files_scanned'     => 10,
				'total_files_with_issues' => 1,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

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
				'total_violations'        => 300,
				'total_lines'             => 1000,
				'compliance_percentage'   => 70.0,
				'violations_by_type'      => array(),
				'files_with_violations'   => array(),
				'total_files_scanned'     => 10,
				'total_files_with_issues' => 10,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 25, $result['threat_level'] );
	}

	/**
	 * Test files with violations list
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_files_with_violations_list(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'        => 40,
				'total_lines'             => 2000,
				'compliance_percentage'   => 98.0,
				'violations_by_type'      => array(),
				'files_with_violations'   => array(
					array( 'file' => 'theme/functions.php', 'violations' => 20 ),
					array( 'file' => 'plugin/admin.php', 'violations' => 20 ),
				),
				'total_files_scanned'     => 15,
				'total_files_with_issues' => 2,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Coding_Standards::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'files_with_violations', $result['details'] );
		$this->assertCount( 2, $result['details']['files_with_violations'] );
	}
}
