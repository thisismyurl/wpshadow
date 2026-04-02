<?php
/**
 * Tests for WCAG Contrast Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_WCAG_Contrast;
use WP_Mock\Tools\TestCase;

/**
 * WCAG Contrast Test Class
 *
 * @since 1.6093.1200
 */
class WCAGContrastTest extends TestCase {

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
	 * Test diagnostic passes with compliant contrast
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_with_compliant_contrast(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags low contrast violations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_low_contrast_violations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'      => 10,
				'total_elements'        => 100,
				'compliance_percentage' => 90.0,
				'violations'            => array(
					array(
						'foreground'     => '#ccc',
						'background'     => '#fff',
						'contrast_ratio' => 2.5,
						'passes_aa'      => false,
					),
				),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 10, $result['meta']['total_violations'] );
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
				'total_violations'      => 5,
				'total_elements'        => 100,
				'compliance_percentage' => 95.0,
				'violations'            => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

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

		$this->assertEquals( 'wcag-color-contrast', $result['id'] );
		$this->assertEquals( 'design', $result['family'] );
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
				'total_violations'      => 8,
				'total_elements'        => 200,
				'compliance_percentage' => 96.0,
				'violations'            => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'compliance_percentage', $result['meta'] );
		$this->assertEquals( 96.0, $result['meta']['compliance_percentage'] );
	}

	/**
	 * Test meta includes WCAG requirements
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_wcag_requirements(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'      => 5,
				'total_elements'        => 100,
				'compliance_percentage' => 95.0,
				'violations'            => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'wcag_aa_required', $result['meta'] );
		$this->assertArrayHasKey( 'wcag_aaa_required', $result['meta'] );
		$this->assertEquals( 4.5, $result['meta']['wcag_aa_required'] );
		$this->assertEquals( 7.0, $result['meta']['wcag_aaa_required'] );
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
				'total_violations'      => 3,
				'total_elements'        => 100,
				'compliance_percentage' => 97.0,
				'violations'            => array(
					array(
						'foreground'     => '#999',
						'background'     => '#fff',
						'contrast_ratio' => 3.0,
						'passes_aa'      => false,
					),
					array(
						'foreground'     => '#ccc',
						'background'     => '#fff',
						'contrast_ratio' => 2.5,
						'passes_aa'      => false,
					),
				),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

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
				'total_violations'      => 5,
				'total_elements'        => 100,
				'compliance_percentage' => 95.0,
				'violations'            => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

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
				'total_violations'      => 2,
				'total_elements'        => 100,
				'compliance_percentage' => 98.0,
				'violations'            => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertEquals( 50, $result['threat_level'] );
	}

	/**
	 * Test high severity with many violations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_high_severity_with_many_violations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'      => 20,
				'total_elements'        => 100,
				'compliance_percentage' => 80.0,
				'violations'            => array(),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 75, $result['threat_level'] );
	}

	/**
	 * Test contrast ratio in violations
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_contrast_ratio_in_violations(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_violations'      => 1,
				'total_elements'        => 50,
				'compliance_percentage' => 98.0,
				'violations'            => array(
					array(
						'foreground'     => '#666',
						'background'     => '#fff',
						'contrast_ratio' => 4.0,
						'passes_aa'      => false,
					),
				),
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_WCAG_Contrast::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'violations', $result['details'] );
		$this->assertEquals( 4.0, $result['details']['violations'][0]['contrast_ratio'] );
		$this->assertFalse( $result['details']['violations'][0]['passes_aa'] );
	}
}
