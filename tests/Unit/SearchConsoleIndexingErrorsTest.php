<?php
/**
 * Tests for Search Console Indexing Errors Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests
 */

use WPShadow\Diagnostics\Diagnostic_Search_Console_Indexing_Errors;
use WP_Mock\Tools\TestCase;

/**
 * Test Search Console Indexing Errors Diagnostic
 */
class SearchConsoleIndexingErrorsTest extends TestCase {

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
	 * Test diagnostic passes when no indexing data available
	 */
	public function test_passes_when_no_indexing_data() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->andReturn( array() );
		WP_Mock::userFunction( 'class_exists' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'wp_remote_head' )->andReturn( array() );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 404 );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic passes when error rate below 1%
	 */
	public function test_passes_when_error_rate_low() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 5 );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertNull( $result ); // 0.5% error rate is excellent.
	}

	/**
	 * Test diagnostic flags high error rate
	 */
	public function test_flags_high_error_rate() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 850 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 50 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( array() );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'search-console-indexing-errors', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] ); // 10% is high.
		$this->assertArrayHasKey( 'error_percent', $result['meta'] );
		$this->assertEquals( 10, $result['meta']['error_percent'] );
	}

	/**
	 * Test severity calculation
	 */
	public function test_severity_calculation() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Test medium severity (5-10% error rate).
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 70 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 900 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 30 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( array() );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertGreaterThanOrEqual( 55, $result['threat_level'] );
	}

	/**
	 * Test diagnostic structure
	 */
	public function test_diagnostic_structure() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 850 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 50 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( array() );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

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
	 * Test meta includes error types
	 */
	public function test_meta_includes_error_types() {
		$error_types = array(
			'server_error'   => 10,
			'redirect_error' => 5,
			'soft_404'       => 8,
		);

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 850 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 50 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( $error_types );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'sprintf' )->andReturnUsing(
			function( $format, ...$args ) {
				return vsprintf( $format, $args );
			}
		);
		WP_Mock::userFunction( 'ucfirst' )->andReturnUsing( 'ucfirst' );
		WP_Mock::userFunction( 'str_replace' )->andReturnUsing( 'str_replace' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertArrayHasKey( 'error_types', $result['meta'] );
		$this->assertEquals( $error_types, $result['meta']['error_types'] );
	}

	/**
	 * Test details populated
	 */
	public function test_details_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 850 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 50 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( array() );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertIsArray( $result['details'] );
		$this->assertNotEmpty( $result['details'] );
	}

	/**
	 * Test recommendations populated
	 */
	public function test_recommendations_populated() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 1000 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 850 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 50 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( array() );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertNotEmpty( $result['recommendations'] );
		$this->assertGreaterThanOrEqual( 5, count( $result['recommendations'] ) );
	}

	/**
	 * Test caching behavior
	 */
	public function test_caching_behavior() {
		$cached_result = array( 'id' => 'search-console-indexing-errors' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test threat level scales with severity
	 */
	public function test_threat_level_scales() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Test high severity (>10% error rate).
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_total_pages' )->andReturn( 100 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_pages' )->andReturn( 20 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_indexed_pages' )->andReturn( 70 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_excluded_pages' )->andReturn( 10 );
		WP_Mock::userFunction( 'get_option' )->with( 'wpshadow_gsc_error_types' )->andReturn( array() );
		WP_Mock::userFunction( 'get_option' )->andReturn( 0 );

		$result = Diagnostic_Search_Console_Indexing_Errors::check();

		$this->assertEquals( 'high', $result['severity'] );
		$this->assertGreaterThanOrEqual( 65, $result['threat_level'] );
	}
}
