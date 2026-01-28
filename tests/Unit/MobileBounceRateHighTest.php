<?php
/**
 * Tests for Mobile Bounce Rate Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests
 */

use WPShadow\Diagnostics\Diagnostic_Mobile_Bounce_Rate_High;
use WP_Mock\Tools\TestCase;

/**
 * Test Mobile Bounce Rate Diagnostic
 */
class MobileBounceRateHighTest extends TestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	/**
	 * Test diagnostic passes when no analytics data available
	 */
	public function test_passes_when_no_analytics_data() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->andReturn( array() );
		WP_Mock::userFunction( 'class_exists' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( 'wp_get_theme' )->andReturn( (object) array() );
		WP_Mock::userFunction( 'current_theme_supports' )->andReturn( true );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic passes when mobile bounce within acceptable range
	 */
	public function test_passes_when_mobile_bounce_acceptable() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 55 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertNull( $result ); // 10% difference is acceptable.
	}

	/**
	 * Test diagnostic flags high mobile bounce rate
	 */
	public function test_flags_high_mobile_bounce_rate() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 80 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'mobile-bounce-rate-high', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertArrayHasKey( 'mobile_bounce_rate', $result['meta'] );
		$this->assertEquals( 80, $result['meta']['mobile_bounce_rate'] );
		$this->assertEquals( 50, $result['meta']['desktop_bounce_rate'] );
		$this->assertGreaterThan( 50, $result['meta']['difference_percent'] );
	}

	/**
	 * Test severity calculation
	 */
	public function test_severity_calculation() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Test medium severity (30-50% difference).
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 70 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertGreaterThanOrEqual( 50, $result['threat_level'] );
		$this->assertLessThanOrEqual( 60, $result['threat_level'] );
	}

	/**
	 * Test diagnostic structure
	 */
	public function test_diagnostic_structure() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 80 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta data includes analytics source
	 */
	public function test_meta_includes_analytics_source() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 80 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertArrayHasKey( 'analytics_source', $result['meta'] );
		$this->assertEquals( 'wpshadow_custom', $result['meta']['analytics_source'] );
	}

	/**
	 * Test details array is populated
	 */
	public function test_details_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 80 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertIsArray( $result['details'] );
		$this->assertNotEmpty( $result['details'] );
		$this->assertGreaterThanOrEqual( 3, count( $result['details'] ) );
	}

	/**
	 * Test recommendations array is populated
	 */
	public function test_recommendations_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 80 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertNotEmpty( $result['recommendations'] );
		$this->assertGreaterThanOrEqual( 5, count( $result['recommendations'] ) );
	}

	/**
	 * Test caching behavior
	 */
	public function test_caching_behavior() {
		$cached_result = array( 'id' => 'mobile-bounce-rate-high' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test threat level scales with severity
	 */
	public function test_threat_level_scales() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Test high severity (>50% difference).
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_mobile_bounce_rate' )->andReturn( 90 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_desktop_bounce_rate' )->andReturn( 50 );

		$result = Diagnostic_Mobile_Bounce_Rate_High::check();

		$this->assertEquals( 'high', $result['severity'] );
		$this->assertGreaterThanOrEqual( 60, $result['threat_level'] );
	}
}
