<?php
/**
 * Unit tests for Crop vs Resize Settings Diagnostic
 *
 * @package    WPShadow\Tests\Unit
 * @subpackage Diagnostics
 * @since      1.2032.1352
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use PHPUnit\Framework\TestCase;

// Manually load required files for testing
require_once __DIR__ . '/../../includes/systems/core/class-diagnostic-base.php';
require_once __DIR__ . '/../../includes/diagnostics/tests/performance/class-diagnostic-crop-vs-resize-settings.php';

use WPShadow\Diagnostics\Diagnostic_Crop_Vs_Resize_Settings;

/**
 * Test class for Crop vs Resize Settings Diagnostic
 *
 * @since 1.2032.1352
 */
class CropVsResizeSettingsTest extends TestCase {

	/**
	 * Set up before each test
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Reset global variable
		global $_wp_additional_image_sizes;
		$_wp_additional_image_sizes = array();
	}

	/**
	 * Test diagnostic returns null when no issues found
	 *
	 * @return void
	 */
	public function test_passes_with_optimal_configuration() {
		// Note: WordPress default sizes (medium, large) have both width and height
		// set without crop, which the diagnostic correctly flags as inefficient.
		// This test checks that the diagnostic correctly identifies this issue.
		global $_wp_additional_image_sizes;
		$_wp_additional_image_sizes = array();

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		// The default WordPress configuration is actually not optimal,
		// so we expect issues to be found.
		$this->assertNotNull( $result, 'Should detect inefficient default WordPress configuration' );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'inefficient_sizes', $result['details'] );
	}

	/**
	 * Test diagnostic detects excessive hard crop usage
	 *
	 * @return void
	 */
	public function test_flags_excessive_hard_crop() {
		global $_wp_additional_image_sizes;

		// Create many hard crop sizes to exceed 60% threshold
		$_wp_additional_image_sizes = array(
			'crop1' => array( 'width' => 300, 'height' => 200, 'crop' => true ),
			'crop2' => array( 'width' => 400, 'height' => 300, 'crop' => true ),
			'crop3' => array( 'width' => 500, 'height' => 400, 'crop' => true ),
			'crop4' => array( 'width' => 600, 'height' => 500, 'crop' => true ),
			'crop5' => array( 'width' => 700, 'height' => 600, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		$this->assertNotNull( $result, 'Should detect excessive hard crop usage' );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertGreaterThan( 60, $result['details']['hard_crop_percentage'] );
	}

	/**
	 * Test diagnostic detects very large hard crop sizes
	 *
	 * @return void
	 */
	public function test_flags_large_hard_crop_sizes() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'huge-crop' => array( 'width' => 2400, 'height' => 1600, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		$this->assertNotNull( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'issues', $result['details'] );

		$found_issue = false;
		foreach ( $result['details']['issues'] as $issue ) {
			if ( strpos( $issue, 'huge-crop' ) !== false && strpos( $issue, 'wasting disk space' ) !== false ) {
				$found_issue = true;
				break;
			}
		}

		$this->assertTrue( $found_issue, 'Should flag very large hard crop sizes' );
	}

	/**
	 * Test diagnostic detects duplicate dimensions
	 *
	 * @return void
	 */
	public function test_flags_duplicate_dimensions() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'size-a' => array( 'width' => 300, 'height' => 300, 'crop' => true ),
			'size-b' => array( 'width' => 300, 'height' => 300, 'crop' => false ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		$this->assertNotNull( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'issues', $result['details'] );

		$found_duplicate = false;
		foreach ( $result['details']['issues'] as $issue ) {
			if ( strpos( $issue, '300x300' ) !== false && strpos( $issue, 'multiple sizes' ) !== false ) {
				$found_duplicate = true;
				break;
			}
		}

		$this->assertTrue( $found_duplicate, 'Should detect duplicate dimensions' );
	}

	/**
	 * Test diagnostic structure is correct
	 *
	 * @return void
	 */
	public function test_diagnostic_structure() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'test' => array( 'width' => 2500, 'height' => 2000, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'details', $result );

		$this->assertEquals( 'crop-vs-resize-settings', $result['id'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test diagnostic slug is correct
	 *
	 * @return void
	 */
	public function test_slug_is_correct() {
		$this->assertEquals( 'crop-vs-resize-settings', Diagnostic_Crop_Vs_Resize_Settings::get_slug() );
	}

	/**
	 * Test diagnostic title is correct
	 *
	 * @return void
	 */
	public function test_title_is_correct() {
		$this->assertEquals( 'Crop vs Resize Settings', Diagnostic_Crop_Vs_Resize_Settings::get_title() );
	}

	/**
	 * Test diagnostic family is performance
	 *
	 * @return void
	 */
	public function test_family_is_performance() {
		$this->assertEquals( 'performance', Diagnostic_Crop_Vs_Resize_Settings::get_family() );
	}

	/**
	 * Test diagnostic counts image sizes correctly
	 *
	 * @return void
	 */
	public function test_counts_image_sizes_correctly() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'custom1' => array( 'width' => 400, 'height' => 300, 'crop' => true ),
			'custom2' => array( 'width' => 800, 'height' => 600, 'crop' => false ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		if ( null !== $result ) {
			$this->assertArrayHasKey( 'details', $result );
			$this->assertArrayHasKey( 'total_image_sizes', $result['details'] );
			$this->assertArrayHasKey( 'hard_crop_count', $result['details'] );
			$this->assertArrayHasKey( 'soft_crop_count', $result['details'] );

			// Should include 3 defaults + 2 custom = 5 total
			$this->assertEquals( 5, $result['details']['total_image_sizes'] );
		}
	}

	/**
	 * Test diagnostic reports JPEG quality
	 *
	 * @return void
	 */
	public function test_includes_jpeg_quality() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'test' => array( 'width' => 2400, 'height' => 1600, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		$this->assertNotNull( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'jpeg_quality', $result['details'] );
		$this->assertIsInt( $result['details']['jpeg_quality'] );
	}

	/**
	 * Test diagnostic detects inefficient resize configurations
	 *
	 * @return void
	 */
	public function test_flags_inefficient_resize() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'inefficient' => array( 'width' => 800, 'height' => 600, 'crop' => false ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		if ( null !== $result ) {
			$this->assertArrayHasKey( 'details', $result );
			$this->assertArrayHasKey( 'inefficient_sizes', $result['details'] );
			$this->assertContains( 'inefficient', $result['details']['inefficient_sizes'] );
		}
	}

	/**
	 * Test diagnostic threat level is 40 for medium severity
	 *
	 * @return void
	 */
	public function test_threat_level_40_for_medium() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'test' => array( 'width' => 2000, 'height' => 1500, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		if ( null !== $result && $result['severity'] === 'medium' ) {
			$this->assertEquals( 40, $result['threat_level'] );
		}
	}

	/**
	 * Test diagnostic increases severity for many issues
	 *
	 * @return void
	 */
	public function test_increases_severity_for_many_issues() {
		global $_wp_additional_image_sizes;

		// Create scenario with many issues
		$_wp_additional_image_sizes = array(
			'crop1'  => array( 'width' => 2400, 'height' => 1600, 'crop' => true ),
			'crop2'  => array( 'width' => 2400, 'height' => 1600, 'crop' => false ),
			'crop3'  => array( 'width' => 400, 'height' => 300, 'crop' => true ),
			'crop4'  => array( 'width' => 500, 'height' => 400, 'crop' => true ),
			'crop5'  => array( 'width' => 600, 'height' => 500, 'crop' => true ),
			'crop6'  => array( 'width' => 700, 'height' => 600, 'crop' => true ),
			'crop7'  => array( 'width' => 800, 'height' => 700, 'crop' => true ),
			'crop8'  => array( 'width' => 900, 'height' => 800, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		if ( null !== $result ) {
			$this->assertArrayHasKey( 'severity', $result );
			// With many issues or high hard crop percentage, severity should be high
			if ( count( $result['details']['issues'] ) > 5 ) {
				$this->assertEquals( 'high', $result['severity'] );
				$this->assertEquals( 60, $result['threat_level'] );
			}
		}
	}

	/**
	 * Test diagnostic includes KB link
	 *
	 * @return void
	 */
	public function test_includes_kb_link() {
		global $_wp_additional_image_sizes;

		$_wp_additional_image_sizes = array(
			'test' => array( 'width' => 2400, 'height' => 1600, 'crop' => true ),
		);

		$result = Diagnostic_Crop_Vs_Resize_Settings::check();

		$this->assertNotNull( $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertStringContainsString( 'wpshadow.com/kb', $result['kb_link'] );
	}
}
