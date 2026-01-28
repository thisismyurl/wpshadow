<?php
/**
 * Tests for Lazy Loading Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6028.1550
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Lazy_Loading;
use WP_Mock\Tools\TestCase;

/**
 * Lazy Loading Diagnostic Test Class
 *
 * @since 1.6028.1550
 */
class LazyLoadingTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes when lazy loading is implemented
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_passes_with_lazy_loading(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 9,
				'eager_images'    => array( 'hero.jpg' ),
				'lazy_percentage' => 90.0,
			),
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags missing lazy loading
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_flags_missing_lazy_loading(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'img1.jpg', 'img2.jpg', 'img3.jpg', 'img4.jpg', 'img5.jpg', 'img6.jpg', 'img7.jpg', 'img8.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Only 20% of 10 images use lazy loading',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'lazy-loading-not-implemented', $result['id'] );
	}

	/**
	 * Test diagnostic passes with no images
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_passes_with_no_images(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 0,
				'lazy_images'     => 0,
				'eager_images'    => array(),
				'lazy_percentage' => 0,
			),
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertNull( $result );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'img1.jpg', 'img2.jpg', 'img3.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

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

		$this->assertEquals( 'lazy-loading-not-implemented', $result['id'] );
		$this->assertEquals( 'performance', $result['family'] );
		$this->assertTrue( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes image statistics
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_meta_includes_image_statistics(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 15,
				'lazy_images'     => 5,
				'eager_images'    => array_fill( 0, 10, 'test.jpg' ),
				'lazy_percentage' => 33.3,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'total_images', $result['meta'] );
		$this->assertArrayHasKey( 'lazy_images', $result['meta'] );
		$this->assertArrayHasKey( 'eager_images_count', $result['meta'] );
		$this->assertArrayHasKey( 'lazy_percentage', $result['meta'] );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
	}

	/**
	 * Test details include implementation methods
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_details_include_implementation_methods(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'test.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'implementation_methods', $result['details'] );
		$this->assertArrayHasKey( 'native', $result['details']['implementation_methods'] );
	}

	/**
	 * Test details include code examples
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_details_include_code_examples(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'test.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'code_example', $result['details'] );
		$this->assertStringContainsString( 'loading="lazy"', $result['details']['code_example'] );
	}

	/**
	 * Test details include exceptions
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_details_include_exceptions(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'test.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'exceptions', $result['details'] );
		$this->assertNotEmpty( $result['details']['exceptions'] );
	}

	/**
	 * Test threat level increases with more eager images
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_threat_level_increases_with_eager_images(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 25,
				'lazy_images'     => 2,
				'eager_images'    => array_fill( 0, 23, 'test.jpg' ),
				'lazy_percentage' => 8.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 45, $result['threat_level'] );
	}

	/**
	 * Test performance impact reflects severity
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_performance_impact_reflects_severity(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 20,
				'lazy_images'     => 2,
				'eager_images'    => array_fill( 0, 18, 'test.jpg' ),
				'lazy_percentage' => 10.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
		$this->assertStringContainsString( 'impact', strtolower( $result['meta']['performance_impact'] ) );
	}

	/**
	 * Test details include Core Web Vitals impact
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_details_include_core_web_vitals_impact(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'test.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'core_web_vitals_impact', $result['details'] );
		$this->assertArrayHasKey( 'lcp', $result['details']['core_web_vitals_impact'] );
		$this->assertArrayHasKey( 'cls', $result['details']['core_web_vitals_impact'] );
	}

	/**
	 * Test details include browser support information
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_details_include_browser_support(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'test.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'browser_support', $result['details'] );
	}

	/**
	 * Test details include why it matters
	 *
	 * @since 1.6028.1550
	 * @return void
	 */
	public function test_details_include_why_matters(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'total_images'    => 10,
				'lazy_images'     => 2,
				'eager_images'    => array( 'test.jpg' ),
				'lazy_percentage' => 20.0,
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test message',
		) );

		$result = Diagnostic_Lazy_Loading::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'why_matters', $result['details'] );
		$this->assertNotEmpty( $result['details']['why_matters'] );
	}
}
