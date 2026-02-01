<?php
/**
 * Tests for Custom Image Sizes Registration Diagnostic
 *
 * phpcs:disable WordPress.Files.FileName.InvalidClassFileName
 * phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 *
 * @package WPShadow\Tests\Unit
 * @since   1.6032.0852
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Custom_Image_Sizes_Registration;
use WPShadow\Tests\TestCase;

// Manually load the diagnostic class for testing
require_once __DIR__ . '/../../includes/diagnostics/tests/performance/class-diagnostic-custom-image-sizes-registration.php';

/**
 * Custom Image Sizes Registration Diagnostic tests
 *
 * Tests the diagnostic that validates custom image size registrations
 * from themes and plugins.
 */
class CustomImageSizesRegistrationTest extends TestCase {

	/**
	 * Test diagnostic returns null when no custom sizes registered
	 *
	 * @return void
	 */
	public function testReturnsNullWithNoCustomSizes(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Clear custom sizes
		$_wp_additional_image_sizes = array();

		// Mock get_intermediate_image_sizes to return only defaults
		$mock_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );

		// Create a test instance
		$result = $this->mockDiagnosticCheck( $mock_sizes, array(), 0 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic detects excessive custom image sizes
	 *
	 * @return void
	 */
	public function testDetectsExcessiveCustomImageSizes(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create 15 custom sizes (more than recommended 10)
		$custom_sizes               = array();
		$_wp_additional_image_sizes = array();

		for ( $i = 1; $i <= 15; $i++ ) {
			$size_name                                = "custom_size_{$i}";
			$custom_sizes[]                           = $size_name;
			$_wp_additional_image_sizes[ $size_name ] = array(
				'width'  => 300 + ( $i * 50 ),
				'height' => 300 + ( $i * 50 ),
				'crop'   => false,
			);
		}

		// Add WordPress defaults
		$all_sizes = array_merge( array( 'thumbnail', 'medium', 'large' ), $custom_sizes );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 100 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertEquals( 'custom-image-sizes-registration', $result['id'] );
		$this->assertStringContainsString( '15 custom image sizes', $result['description'] );
	}

	/**
	 * Test diagnostic detects large image dimensions
	 *
	 * @return void
	 */
	public function testDetectsLargeImageDimensions(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create sizes with very large dimensions
		$_wp_additional_image_sizes = array(
			'huge_banner' => array(
				'width'  => 3000,
				'height' => 2000,
				'crop'   => true,
			),
			'giant_hero'  => array(
				'width'  => 2500,
				'height' => 1800,
				'crop'   => false,
			),
		);

		$all_sizes = array( 'thumbnail', 'medium', 'huge_banner', 'giant_hero' );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 50 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Large image sizes detected', $result['description'] );
	}

	/**
	 * Test diagnostic detects duplicate dimensions
	 *
	 * @return void
	 */
	public function testDetectsDuplicateDimensions(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create sizes with identical dimensions
		$_wp_additional_image_sizes = array(
			'custom_thumb_1' => array(
				'width'  => 300,
				'height' => 200,
				'crop'   => true,
			),
			'custom_thumb_2' => array(
				'width'  => 300,
				'height' => 200,
				'crop'   => false,
			),
			'another_size'   => array(
				'width'  => 400,
				'height' => 300,
				'crop'   => true,
			),
		);

		$all_sizes = array( 'thumbnail', 'custom_thumb_1', 'custom_thumb_2', 'another_size' );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 50 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Potential duplicate sizes', $result['description'] );
	}

	/**
	 * Test diagnostic detects generic naming conflicts
	 *
	 * @return void
	 */
	public function testDetectsGenericNamingConflicts(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create sizes with generic names
		$_wp_additional_image_sizes = array(
			'small' => array(
				'width'  => 150,
				'height' => 150,
				'crop'   => true,
			),
			'big'   => array(
				'width'  => 800,
				'height' => 600,
				'crop'   => false,
			),
		);

		$all_sizes = array( 'thumbnail', 'medium', 'small', 'big' );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 50 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'Generic size names', $result['description'] );
	}

	/**
	 * Test diagnostic calculates correct threat level for high severity
	 *
	 * @return void
	 */
	public function testCalculatesHighThreatLevel(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create 25 custom sizes (more than 2x recommended)
		$custom_sizes               = array();
		$_wp_additional_image_sizes = array();

		for ( $i = 1; $i <= 25; $i++ ) {
			$size_name                                = "custom_size_{$i}";
			$custom_sizes[]                           = $size_name;
			$_wp_additional_image_sizes[ $size_name ] = array(
				'width'  => 300 + ( $i * 20 ),
				'height' => 300 + ( $i * 20 ),
				'crop'   => false,
			);
		}

		$all_sizes = array_merge( array( 'thumbnail', 'medium' ), $custom_sizes );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 100 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertGreaterThanOrEqual( 75, $result['threat_level'] );
		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test diagnostic calculates medium threat level for moderate issues
	 *
	 * @return void
	 */
	public function testCalculatesMediumThreatLevel(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create 12 custom sizes (slightly over recommended)
		$custom_sizes               = array();
		$_wp_additional_image_sizes = array();

		for ( $i = 1; $i <= 12; $i++ ) {
			$size_name                                = "custom_size_{$i}";
			$custom_sizes[]                           = $size_name;
			$_wp_additional_image_sizes[ $size_name ] = array(
				'width'  => 300,
				'height' => 300,
				'crop'   => false,
			);
		}

		$all_sizes = array_merge( array( 'thumbnail', 'medium' ), $custom_sizes );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 100 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertGreaterThanOrEqual( 50, $result['threat_level'] );
		$this->assertLessThan( 75, $result['threat_level'] );
		$this->assertEquals( 'medium', $result['severity'] );
	}

	/**
	 * Test diagnostic returns valid finding structure
	 *
	 * @return void
	 */
	public function testReturnsValidFindingStructure(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create 15 custom sizes to trigger finding
		$custom_sizes               = array();
		$_wp_additional_image_sizes = array();

		for ( $i = 1; $i <= 15; $i++ ) {
			$size_name                                = "test_size_{$i}";
			$custom_sizes[]                           = $size_name;
			$_wp_additional_image_sizes[ $size_name ] = array(
				'width'  => 400,
				'height' => 300,
				'crop'   => true,
			);
		}

		$all_sizes = array_merge( array( 'thumbnail' ), $custom_sizes );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 100 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertEquals( 'custom-image-sizes-registration', $result['id'] );
		$this->assertEquals( 'Custom Image Sizes Registration', $result['title'] );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertFalse( $result['auto_fixable'] );
		$this->assertArrayHasKey( 'kb_link', $result );
	}

	/**
	 * Test diagnostic with optimal configuration returns null
	 *
	 * @return void
	 */
	public function testOptimalConfigurationReturnsNull(): void {
		global $_wp_additional_image_sizes;

		// Save current state
		$original_sizes = $_wp_additional_image_sizes;

		// Create 5 well-configured custom sizes (within limits)
		$_wp_additional_image_sizes = array(
			'product_thumbnail' => array(
				'width'  => 300,
				'height' => 300,
				'crop'   => true,
			),
			'product_large'     => array(
				'width'  => 800,
				'height' => 800,
				'crop'   => false,
			),
			'hero_banner'       => array(
				'width'  => 1200,
				'height' => 600,
				'crop'   => true,
			),
			'blog_featured'     => array(
				'width'  => 600,
				'height' => 400,
				'crop'   => true,
			),
			'sidebar_widget'    => array(
				'width'  => 250,
				'height' => 200,
				'crop'   => false,
			),
		);

		$all_sizes = array( 'thumbnail', 'medium', 'product_thumbnail', 'product_large', 'hero_banner', 'blog_featured', 'sidebar_widget' );

		$result = $this->mockDiagnosticCheck( $all_sizes, $_wp_additional_image_sizes, 50 );

		// Restore state
		$_wp_additional_image_sizes = $original_sizes;

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic metadata getters
	 *
	 * @return void
	 */
	public function testDiagnosticMetadata(): void {
		$this->assertEquals( 'custom-image-sizes-registration', Diagnostic_Custom_Image_Sizes_Registration::get_slug() );
		$this->assertEquals( 'Custom Image Sizes Registration', Diagnostic_Custom_Image_Sizes_Registration::get_title() );
		$this->assertEquals( 'Tests custom image sizes from themes/plugins. Validates add_image_size calls.', Diagnostic_Custom_Image_Sizes_Registration::get_description() );
		$this->assertEquals( 'performance', Diagnostic_Custom_Image_Sizes_Registration::get_family() );
	}

	/**
	 * Mock the diagnostic check with test data
	 *
	 * This helper simulates the WordPress environment for testing.
	 *
	 * @param array $all_sizes            All image sizes.
	 * @param array $additional_sizes     Additional image sizes data.
	 * @param int   $attachment_count     Number of attachments.
	 * @return array|null Finding result or null.
	 */
	private function mockDiagnosticCheck( array $all_sizes, array $additional_sizes, int $attachment_count ) {
		global $_wp_additional_image_sizes, $wpdb;

		// Mock get_intermediate_image_sizes
		add_filter(
			'intermediate_image_sizes',
			function () use ( $all_sizes ) {
				return $all_sizes;
			}
		);

		// Set global for additional sizes
		$_wp_additional_image_sizes = $additional_sizes;

		// Mock wpdb for attachment count
		$wpdb = new class( $attachment_count ) {
			public $posts = 'wp_posts';
			private $count;

			public function __construct( $count ) {
				$this->count = $count;
			}

			public function get_var( $query ) {
				// Return attachment count for any COUNT query
				if ( strpos( $query, 'SELECT COUNT(*)' ) !== false ) {
					return $this->count;
				}
				return 0;
			}
		};

		// Run the check
		$result = Diagnostic_Custom_Image_Sizes_Registration::check();

		// Clean up filters
		remove_all_filters( 'intermediate_image_sizes' );
		remove_all_filters( 'query' );

		return $result;
	}
}
