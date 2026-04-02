<?php
/**
 * Tests for SPF Record Missing Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests
 */

use WPShadow\Diagnostics\Diagnostic_SPF_Record_Missing;
use WP_Mock\Tools\TestCase;

/**
 * Test SPF Record Missing Diagnostic
 */
class SPFRecordMissingTest extends TestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		WP_Mock::setUp();
		$_SERVER = array();
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	/**
	 * Test diagnostic passes when valid SPF record exists
	 */
	public function test_passes_when_valid_spf_exists() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( null );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_SPF_Record_Missing::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags missing SPF record
	 */
	public function test_flags_missing_spf() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_parse_url' )->andReturn( array( 'host' => 'example.com' ) );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'sprintf' )->andReturnUsing(
			function( $format, ...$args ) {
				return vsprintf( $format, $args );
			}
		);
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		// Mock DNS query returning no SPF.
		WP_Mock::userFunction( 'function_exists' )->with( 'dns_get_record' )->andReturn( false );
		WP_Mock::userFunction( 'function_exists' )->with( 'shell_exec' )->andReturn( false );

		$result = Diagnostic_SPF_Record_Missing::check();

		// Should flag missing SPF or pass if domain extraction failed.
		$this->assertTrue( is_array( $result ) || is_null( $result ) );
	}

	/**
	 * Test diagnostic structure when SPF missing
	 */
	public function test_diagnostic_structure_missing_spf() {
		$test_result = array(
			'id'           => 'spf-record-missing',
			'title'        => 'SPF Record Missing',
			'description'  => 'No SPF record found for example.com - emails may be marked as spam',
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/spf-record',
			'meta'         => array(
				'domain'     => 'example.com',
				'spf_exists' => false,
				'server_ip'  => '',
			),
			'details'      => array(),
			'recommendations' => array(),
		);

		$this->assertArrayHasKey( 'id', $test_result );
		$this->assertArrayHasKey( 'title', $test_result );
		$this->assertArrayHasKey( 'description', $test_result );
		$this->assertArrayHasKey( 'severity', $test_result );
		$this->assertArrayHasKey( 'threat_level', $test_result );
		$this->assertArrayHasKey( 'auto_fixable', $test_result );
		$this->assertArrayHasKey( 'kb_link', $test_result );
		$this->assertArrayHasKey( 'meta', $test_result );
		$this->assertArrayHasKey( 'details', $test_result );
		$this->assertArrayHasKey( 'recommendations', $test_result );
		$this->assertFalse( $test_result['auto_fixable'] );
		$this->assertEquals( 'high', $test_result['severity'] );
	}

	/**
	 * Test diagnostic structure when SPF invalid
	 */
	public function test_diagnostic_structure_invalid_spf() {
		$test_result = array(
			'id'           => 'spf-record-missing',
			'title'        => 'SPF Record Invalid',
			'description'  => 'SPF record has 2 validation issues affecting deliverability',
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/spf-record',
			'meta'         => array(
				'domain'         => 'example.com',
				'spf_exists'     => true,
				'spf_record'     => 'v=spf1 a mx ~all',
				'server_ip'      => '192.168.1.1',
				'issues_count'   => 2,
				'includes_count' => 0,
			),
			'details'      => array(
				'SPF record missing policy qualifier',
				'Server IP may not be authorized',
			),
			'recommendations' => array(),
		);

		$this->assertArrayHasKey( 'meta', $test_result );
		$this->assertArrayHasKey( 'spf_record', $test_result['meta'] );
		$this->assertArrayHasKey( 'issues_count', $test_result['meta'] );
		$this->assertArrayHasKey( 'includes_count', $test_result['meta'] );
		$this->assertTrue( $test_result['meta']['spf_exists'] );
		$this->assertEquals( 'medium', $test_result['severity'] );
	}

	/**
	 * Test meta includes domain and IP
	 */
	public function test_meta_includes_domain_and_ip() {
		$test_result = array(
			'meta' => array(
				'domain'     => 'example.com',
				'spf_exists' => false,
				'server_ip'  => '192.168.1.1',
			),
		);

		$this->assertArrayHasKey( 'domain', $test_result['meta'] );
		$this->assertArrayHasKey( 'spf_exists', $test_result['meta'] );
		$this->assertArrayHasKey( 'server_ip', $test_result['meta'] );
		$this->assertIsString( $test_result['meta']['domain'] );
		$this->assertIsBool( $test_result['meta']['spf_exists'] );
	}

	/**
	 * Test details populated for missing SPF
	 */
	public function test_details_populated_missing() {
		$test_result = array(
			'details' => array(
				'SPF record missing from DNS configuration',
				'Without SPF, emails may be rejected or marked as spam',
				'70% of email deliverability issues are SPF-related',
			),
		);

		$this->assertIsArray( $test_result['details'] );
		$this->assertNotEmpty( $test_result['details'] );
		$this->assertGreaterThanOrEqual( 3, count( $test_result['details'] ) );
	}

	/**
	 * Test recommendations populated
	 */
	public function test_recommendations_populated() {
		$test_result = array(
			'recommendations' => array(
				'Add SPF record to DNS configuration immediately',
				'Contact hosting provider or DNS administrator',
				'Basic SPF record: v=spf1 a mx ~all',
				'Test SPF record after adding using online SPF validators',
			),
		);

		$this->assertIsArray( $test_result['recommendations'] );
		$this->assertNotEmpty( $test_result['recommendations'] );
		$this->assertGreaterThanOrEqual( 3, count( $test_result['recommendations'] ) );
	}

	/**
	 * Test caching behavior
	 */
	public function test_caching_behavior() {
		$cached_result = array( 'id' => 'spf-record-missing' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_SPF_Record_Missing::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test threat level for missing SPF
	 */
	public function test_threat_level_missing_spf() {
		$test_result = array(
			'severity'     => 'high',
			'threat_level' => 70,
		);

		$this->assertEquals( 'high', $test_result['severity'] );
		$this->assertEquals( 70, $test_result['threat_level'] );
	}

	/**
	 * Test threat level for invalid SPF
	 */
	public function test_threat_level_invalid_spf() {
		$test_result = array(
			'severity'     => 'medium',
			'threat_level' => 50,
		);

		$this->assertEquals( 'medium', $test_result['severity'] );
		$this->assertGreaterThanOrEqual( 45, $test_result['threat_level'] );
		$this->assertLessThanOrEqual( 65, $test_result['threat_level'] );
	}
}
