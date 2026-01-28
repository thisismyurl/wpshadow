<?php
/**
 * Tests for 404 Error Rate Diagnostic
 *
 * @package WPShadow\Tests
 * @since   1.6028.2115
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_404_Error_Rate;
use WP_Mock\Tools\TestCase;

/**
 * 404 Error Rate Test Class
 *
 * @since 1.6028.2115
 */
class FourZeroFourErrorRateTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Test diagnostic passes with low 404 rate
	 *
	 * @return void
	 */
	public function test_passes_with_low_404_rate() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags high 404 rate
	 *
	 * @return void
	 */
	public function test_flags_high_404_rate() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		
		// Return 404 for 60% of requests.
		$call_count = 0;
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturnUsing(
			function() use ( &$call_count ) {
				++$call_count;
				return array();
			}
		);
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturnUsing(
			function() use ( &$call_count ) {
				return $call_count <= 6 ? 404 : 200;
			}
		);
		
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertIsArray( $result );
		$this->assertEquals( '404-error-rate', $result['id'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @return void
	 */
	public function test_finding_structure_valid() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

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
		$this->assertArrayHasKey( 'recommendations', $result );
	}

	/**
	 * Test meta includes error rate statistics
	 *
	 * @return void
	 */
	public function test_meta_includes_error_rate_stats() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertArrayHasKey( 'total_urls_tested', $result['meta'] );
		$this->assertArrayHasKey( 'error_count', $result['meta'] );
		$this->assertArrayHasKey( 'error_rate', $result['meta'] );
		$this->assertArrayHasKey( 'thresholds', $result['meta'] );
	}

	/**
	 * Test details include error URLs list
	 *
	 * @return void
	 */
	public function test_details_include_error_urls() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertArrayHasKey( 'error_urls', $result['details'] );
		$this->assertIsArray( $result['details']['error_urls'] );
	}

	/**
	 * Test recommendations are included
	 *
	 * @return void
	 */
	public function test_recommendations_included() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertGreaterThan( 0, count( $result['recommendations'] ) );
	}

	/**
	 * Test severity scales with error rate
	 *
	 * @return void
	 */
	public function test_severity_scales_with_error_rate() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertContains( $result['severity'], array( 'low', 'medium', 'high' ) );
		$this->assertIsInt( $result['threat_level'] );
		$this->assertGreaterThanOrEqual( 10, $result['threat_level'] );
		$this->assertLessThanOrEqual( 100, $result['threat_level'] );
	}

	/**
	 * Test high severity with critical error rate
	 *
	 * @return void
	 */
	public function test_high_severity_with_critical_rate() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		// With all 404s, rate should be 100% (critical).
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 70, $result['threat_level'] );
	}

	/**
	 * Test caching behavior
	 *
	 * @return void
	 */
	public function test_caching_behavior() {
		$cached_result = array(
			'id'          => '404-error-rate',
			'error_rate' => 8.5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test auto-fixable is false
	 *
	 * @return void
	 */
	public function test_auto_fixable_is_false() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		\WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_404_Error_Rate::check();

		$this->assertFalse( $result['auto_fixable'] );
	}
}
