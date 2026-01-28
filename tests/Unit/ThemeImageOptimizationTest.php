<?php
/**
 * Tests for Theme Image Optimization Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6028.1720
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Theme_Image_Optimization;
use WP_Mock\Tools\TestCase;

/**
 * Theme Image Optimization Test Class
 *
 * @since 1.6028.1720
 */
class ThemeImageOptimizationTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes with optimized images
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_passes_with_optimized_images(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => null,
		) );

		\WP_Mock::userFunction( 'set_transient' );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags unoptimized images
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_flags_unoptimized_images(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array(
						'path'               => '/assets/img/large.jpg',
						'file_size'          => 512000,
						'width'              => 2000,
						'height'             => 1500,
						'needs_optimization' => true,
						'potential_savings'  => 204800,
					),
				),
				'webp_missing'       => array(),
				'total_images'       => 15,
				'total_size'         => 512000,
				'potential_savings'  => 204800,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Found %1$d unoptimized image',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'theme-image-optimization', $result['id'] );
		$this->assertEquals( 1, $result['meta']['unoptimized_count'] );
	}

	/**
	 * Test diagnostic flags missing WebP
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_flags_missing_webp(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array(
						'path'               => '/img/test.jpg',
						'file_size'          => 150000,
						'needs_optimization' => true,
						'missing_webp'       => true,
					),
				),
				'webp_missing'       => array(
					array( 'path' => '/img/test.jpg' ),
				),
				'total_images'       => 10,
				'total_size'         => 150000,
				'potential_savings'  => 45000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['webp_missing_count'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array( 'path' => '/test.jpg', 'file_size' => 200000, 'needs_optimization' => true ),
				),
				'webp_missing'       => array(),
				'total_images'       => 5,
				'total_size'         => 200000,
				'potential_savings'  => 60000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

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

		$this->assertEquals( 'theme-image-optimization', $result['id'] );
		$this->assertEquals( 'performance', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes image statistics
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_meta_includes_image_statistics(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array( 'path' => '/img1.jpg', 'file_size' => 300000, 'needs_optimization' => true ),
					array( 'path' => '/img2.jpg', 'file_size' => 250000, 'needs_optimization' => true ),
				),
				'webp_missing'       => array( array( 'path' => '/img1.jpg' ) ),
				'total_images'       => 20,
				'total_size'         => 550000,
				'potential_savings'  => 165000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertEquals( 20, $result['meta']['total_images'] );
		$this->assertEquals( 2, $result['meta']['unoptimized_count'] );
		$this->assertEquals( 550000, $result['meta']['total_size'] );
		$this->assertEquals( 165000, $result['meta']['potential_savings'] );
	}

	/**
	 * Test details include top unoptimized list
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_details_include_top_unoptimized(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array( 'path' => '/large1.jpg', 'file_size' => 800000, 'needs_optimization' => true ),
					array( 'path' => '/large2.jpg', 'file_size' => 600000, 'needs_optimization' => true ),
				),
				'webp_missing'       => array(),
				'total_images'       => 10,
				'total_size'         => 1400000,
				'potential_savings'  => 420000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'top_unoptimized', $result['details'] );
		$this->assertNotEmpty( $result['details']['top_unoptimized'] );
	}

	/**
	 * Test recommendations included
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_recommendations_included(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array( 'path' => '/test.jpg', 'file_size' => 200000, 'needs_optimization' => true ),
				),
				'webp_missing'       => array(),
				'total_images'       => 8,
				'total_size'         => 200000,
				'potential_savings'  => 60000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'recommendations', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommendations'] );
	}

	/**
	 * Test severity scales with count
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_severity_scales_with_count(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array_fill( 0, 12, array( 'path' => '/test.jpg', 'file_size' => 200000, 'needs_optimization' => true ) ),
				'webp_missing'       => array(),
				'total_images'       => 20,
				'total_size'         => 2400000,
				'potential_savings'  => 720000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 30, $result['threat_level'] );
	}

	/**
	 * Test potential savings calculation
	 *
	 * @since 1.6028.1720
	 * @return void
	 */
	public function test_potential_savings_calculation(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'unoptimized_images' => array(
					array( 'path' => '/large.jpg', 'file_size' => 1000000, 'needs_optimization' => true, 'potential_savings' => 400000 ),
				),
				'webp_missing'       => array(),
				'total_images'       => 5,
				'total_size'         => 1000000,
				'potential_savings'  => 400000,
			),
		) );

		\WP_Mock::userFunction( '_n', array(
			'return' => 'Test',
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		$result = Diagnostic_Theme_Image_Optimization::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 400000, $result['meta']['potential_savings'] );
	}
}
