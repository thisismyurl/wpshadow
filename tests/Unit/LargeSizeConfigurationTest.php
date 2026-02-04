<?php
/**
 * Tests for Large Size Configuration Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since      1.6032.1352
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Tests\TestCase;

// Load the diagnostic class
require_once WPSHADOW_PLUGIN_DIR . '/includes/diagnostics/tests/admin/class-diagnostic-large-size-configuration.php';

use WPShadow\Diagnostics\Diagnostic_Large_Size_Configuration;

/**
 * Large Size Configuration Test Class
 *
 * @since 1.6032.1352
 */
class LargeSizeConfigurationTest extends TestCase {

/**
 * Test diagnostic passes with properly configured large size
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_passes_with_proper_configuration(): void {
// Mock WordPress large size settings
update_option( 'large_size_w', 1024 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertNull( $result );
}

/**
 * Test diagnostic detects when large size is completely disabled
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_flags_disabled_large_size(): void {
// Both dimensions set to 0
update_option( 'large_size_w', 0 );
update_option( 'large_size_h', 0 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 'large-size-configuration', $result['id'] );
$this->assertEquals( 'medium', $result['severity'] );
$this->assertEquals( 40, $result['threat_level'] );
$this->assertEquals( 'disabled', $result['meta']['issue_type'] );
$this->assertEquals( 0, $result['meta']['current_width'] );
$this->assertEquals( 0, $result['meta']['current_height'] );
}

/**
 * Test diagnostic detects excessively large dimensions
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_flags_excessive_dimensions(): void {
// Width exceeds 4000px
update_option( 'large_size_w', 5000 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 'large-size-configuration', $result['id'] );
$this->assertEquals( 'medium', $result['severity'] );
$this->assertEquals( 40, $result['threat_level'] );
$this->assertEquals( 'excessive', $result['meta']['issue_type'] );
$this->assertEquals( 5000, $result['meta']['current_width'] );
$this->assertEquals( 4000, $result['meta']['max_recommended'] );
}

/**
 * Test diagnostic detects when height is excessively large
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_flags_excessive_height(): void {
update_option( 'large_size_w', 1024 );
update_option( 'large_size_h', 6000 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 'large-size-configuration', $result['id'] );
$this->assertEquals( 'excessive', $result['meta']['issue_type'] );
$this->assertEquals( 6000, $result['meta']['current_height'] );
}

/**
 * Test diagnostic detects dimensions that are too small
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_flags_too_small_dimensions(): void {
// Width is less than 512px
update_option( 'large_size_w', 300 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 'large-size-configuration', $result['id'] );
$this->assertEquals( 'low', $result['severity'] );
$this->assertEquals( 40, $result['threat_level'] );
$this->assertEquals( 'too_small', $result['meta']['issue_type'] );
$this->assertEquals( 300, $result['meta']['current_width'] );
$this->assertEquals( 512, $result['meta']['min_recommended'] );
}

/**
 * Test diagnostic detects when height is too small
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_flags_too_small_height(): void {
update_option( 'large_size_w', 1024 );
update_option( 'large_size_h', 400 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 'too_small', $result['meta']['issue_type'] );
$this->assertEquals( 400, $result['meta']['current_height'] );
}

/**
 * Test finding structure is valid
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_finding_structure_valid(): void {
update_option( 'large_size_w', 0 );
update_option( 'large_size_h', 0 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertValidFinding( $result );
$this->assertEquals( 'large-size-configuration', $result['id'] );
$this->assertEquals( 'admin', $result['family'] );
$this->assertTrue( $result['auto_fixable'] );
$this->assertEquals( 40, $result['threat_level'] );
}

/**
 * Test meta includes current dimensions
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_meta_includes_current_dimensions(): void {
update_option( 'large_size_w', 200 );
update_option( 'large_size_h', 800 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertArrayHasKey( 'meta', $result );
$this->assertEquals( 200, $result['meta']['current_width'] );
$this->assertEquals( 800, $result['meta']['current_height'] );
$this->assertArrayHasKey( 'issue_type', $result['meta'] );
}

/**
 * Test diagnostic passes with width at boundary (512px)
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_passes_with_minimum_width(): void {
update_option( 'large_size_w', 512 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertNull( $result );
}

/**
 * Test diagnostic passes with height at boundary (512px)
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_passes_with_minimum_height(): void {
update_option( 'large_size_w', 1024 );
update_option( 'large_size_h', 512 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertNull( $result );
}

/**
 * Test diagnostic passes at maximum boundary (4000px)
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_passes_at_maximum_boundary(): void {
update_option( 'large_size_w', 4000 );
update_option( 'large_size_h', 4000 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertNull( $result );
}

/**
 * Test diagnostic flags when width exceeds maximum by 1px
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_flags_width_over_maximum(): void {
update_option( 'large_size_w', 4001 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 'excessive', $result['meta']['issue_type'] );
}

/**
 * Test diagnostic with realistic typical configuration
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_passes_with_typical_configuration(): void {
// Common WordPress defaults or similar
update_option( 'large_size_w', 2048 );
update_option( 'large_size_h', 2048 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertNull( $result );
}

/**
 * Test threat level is consistent
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_threat_level_consistent(): void {
// Test with disabled size
update_option( 'large_size_w', 0 );
update_option( 'large_size_h', 0 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertEquals( 40, $result['threat_level'] );
}

/**
 * Test auto_fixable flag is set correctly
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_auto_fixable_flag(): void {
update_option( 'large_size_w', 100 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertTrue( $result['auto_fixable'] );
}

/**
 * Test with one dimension at 0 and other valid (should pass)
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_passes_with_one_dimension_zero(): void {
// Width is 0, height is valid (allows unlimited width)
update_option( 'large_size_w', 0 );
update_option( 'large_size_h', 1024 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertNull( $result );
}

/**
 * Test KB link is included
 *
 * @since 1.6032.1352
 * @return void
 */
public function test_kb_link_included(): void {
update_option( 'large_size_w', 0 );
update_option( 'large_size_h', 0 );

$result = Diagnostic_Large_Size_Configuration::check();

$this->assertIsArray( $result );
$this->assertArrayHasKey( 'kb_link', $result );
$this->assertStringContainsString( 'wpshadow.com/kb/', $result['kb_link'] );
}
}
