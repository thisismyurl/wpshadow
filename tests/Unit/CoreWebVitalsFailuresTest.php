<?php
/**
 * Tests for Core Web Vitals Failures Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests
 */

use WPShadow\Diagnostics\Diagnostic_Core_Web_Vitals_Failures;
use WP_Mock\Tools\TestCase;

/**
 * Test Core Web Vitals Failures Diagnostic
 */
class CoreWebVitalsFailuresTest extends TestCase {

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
	 * Test diagnostic passes when no CWV data available
	 */
	public function test_passes_when_no_cwv_data() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->andReturn( array() );
		WP_Mock::userFunction( 'class_exists' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic passes when passing percentage above 75%
	 */
	public function test_passes_when_passing_above_threshold() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 80 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 10 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 10 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertNull( $result ); // 80% passing is acceptable.
	}

	/**
	 * Test diagnostic flags low passing percentage
	 */
	public function test_flags_low_passing_percentage() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 40 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'core-web-vitals-failures', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] ); // <50% is high severity.
		$this->assertArrayHasKey( 'passing_percent', $result['meta'] );
		$this->assertEquals( 40, $result['meta']['passing_percent'] );
	}

	/**
	 * Test severity calculation
	 */
	public function test_severity_calculation() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Test medium severity (50-75% passing).
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 60 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 20 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 20 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertGreaterThanOrEqual( 50, $result['threat_level'] );
	}

	/**
	 * Test diagnostic structure
	 */
	public function test_diagnostic_structure() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 40 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

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
	 * Test meta includes CWV metrics
	 */
	public function test_meta_includes_cwv_metrics() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 40 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_lcp_issues' )->andReturn( 15 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_fid_issues' )->andReturn( 10 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_cls_issues' )->andReturn( 5 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'sprintf' )->andReturnUsing(
			function( $format, ...$args ) {
				return vsprintf( $format, $args );
			}
		);
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertArrayHasKey( 'lcp_issues', $result['meta'] );
		$this->assertArrayHasKey( 'fid_issues', $result['meta'] );
		$this->assertArrayHasKey( 'cls_issues', $result['meta'] );
		$this->assertEquals( 15, $result['meta']['lcp_issues'] );
		$this->assertEquals( 10, $result['meta']['fid_issues'] );
		$this->assertEquals( 5, $result['meta']['cls_issues'] );
	}

	/**
	 * Test details array populated
	 */
	public function test_details_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 40 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertIsArray( $result['details'] );
		$this->assertNotEmpty( $result['details'] );
	}

	/**
	 * Test recommendations populated
	 */
	public function test_recommendations_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 40 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertNotEmpty( $result['recommendations'] );
		$this->assertGreaterThanOrEqual( 5, count( $result['recommendations'] ) );
	}

	/**
	 * Test caching behavior
	 */
	public function test_caching_behavior() {
		$cached_result = array( 'id' => 'core-web-vitals-failures' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test threat level scales with severity
	 */
	public function test_threat_level_scales() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Test high severity (<50% passing).
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_total_urls' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_good_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_needs_improvement_urls' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_cwv_poor_urls' )->andReturn( 40 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );

		$result = Diagnostic_Core_Web_Vitals_Failures::check();

		$this->assertEquals( 'high', $result['severity'] );
		$this->assertGreaterThanOrEqual( 70, $result['threat_level'] );
	}
}
