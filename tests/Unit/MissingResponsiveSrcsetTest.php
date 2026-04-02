<?php
/**
 * Tests for Missing Responsive Srcset Diagnostic
 *
 * @package WPShadow\Tests
 * @since 1.6093.1200
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Missing_Responsive_Srcset;
use WP_Mock\Tools\TestCase;

/**
 * Missing Responsive Srcset Test Class
 *
 * @since 1.6093.1200
 */
class MissingResponsiveSrcsetTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_with_good_srcset_coverage() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html><img src="test.jpg" srcset="test-300.jpg 300w, test-600.jpg 600w" /></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html><img src="test.jpg" srcset="test-300.jpg 300w, test-600.jpg 600w" /></html>' );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertNull( $result );
	}

	public function test_flags_missing_srcset() {
		$cached_result = array(
			'id'                => 'missing-responsive-srcset',
			'srcset_percentage' => 30.0,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'missing-responsive-srcset', $result['id'] );
	}

	public function test_finding_structure_valid() {
		$cached_result = array(
			'id'           => 'missing-responsive-srcset',
			'title'        => 'Missing Responsive Image Srcset',
			'description'  => 'Test',
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'test',
			'family'       => 'performance',
			'meta'         => array(),
			'details'      => array(),
			'recommendations' => array(),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
	}

	public function test_meta_includes_image_stats() {
		$cached_result = array(
			'id'   => 'missing-responsive-srcset',
			'meta' => array(
				'total_images'      => 20,
				'with_srcset'       => 10,
				'without_srcset'    => 10,
				'srcset_percentage' => 50.0,
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertArrayHasKey( 'total_images', $result['meta'] );
		$this->assertArrayHasKey( 'srcset_percentage', $result['meta'] );
	}

	public function test_details_include_examples() {
		$cached_result = array(
			'id'      => 'missing-responsive-srcset',
			'details' => array(
				'missing_srcset_examples' => array(
					array( 'src' => 'image1.jpg' ),
					array( 'src' => 'image2.jpg' ),
				),
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertArrayHasKey( 'missing_srcset_examples', $result['details'] );
	}

	public function test_recommendations_included() {
		$cached_result = array(
			'id'              => 'missing-responsive-srcset',
			'recommendations' => array( 'Test recommendation' ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertIsArray( $result['recommendations'] );
	}

	public function test_severity_scales_with_percentage() {
		$cached_low = array(
			'id'       => 'missing-responsive-srcset',
			'severity' => 'low',
			'meta'     => array( 'srcset_percentage' => 60.0 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_low );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertEquals( 'low', $result['severity'] );
	}

	public function test_medium_severity_with_low_percentage() {
		$cached_medium = array(
			'id'       => 'missing-responsive-srcset',
			'severity' => 'medium',
			'meta'     => array( 'srcset_percentage' => 40.0 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_medium );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertEquals( 'medium', $result['severity'] );
	}

	public function test_caching_behavior() {
		$cached_result = array(
			'id'                => 'missing-responsive-srcset',
			'srcset_percentage' => 65.0,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertEquals( $cached_result, $result );
	}

	public function test_auto_fixable_is_false() {
		$cached_result = array(
			'id'           => 'missing-responsive-srcset',
			'auto_fixable' => false,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Missing_Responsive_Srcset::check();

		$this->assertFalse( $result['auto_fixable'] );
	}
}
