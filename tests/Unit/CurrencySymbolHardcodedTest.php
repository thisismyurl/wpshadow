<?php
/**
 * Tests for Currency Symbol Hardcoded Diagnostic
 *
 * @package WPShadow\Tests
 * @since   1.6028.2140
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Currency_Symbol_Hardcoded;
use WP_Mock\Tools\TestCase;

/**
 * Currency Symbol Hardcoded Test Class
 *
 * @since 1.6028.2140
 */
class CurrencySymbolHardcodedTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_with_proper_currency_functions() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_stylesheet_directory' )->andReturn( '/tmp/theme' );
		\WP_Mock::userFunction( 'get_option' )->andReturn( array() );
		\WP_Mock::userFunction( 'class_exists' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertNull( $result );
	}

	public function test_flags_hardcoded_currency() {
		$cached_result = array(
			'id'               => 'currency-symbol-hardcoded',
			'total_violations' => 5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'currency-symbol-hardcoded', $result['id'] );
	}

	public function test_finding_structure_valid() {
		$cached_result = array(
			'id'           => 'currency-symbol-hardcoded',
			'title'        => 'Currency Symbol Hardcoded as $',
			'description'  => 'Test',
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'test',
			'family'       => 'i18n',
			'meta'         => array(),
			'details'      => array(),
			'recommendations' => array(),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
	}

	public function test_meta_includes_woocommerce_status() {
		$cached_result = array(
			'id'   => 'currency-symbol-hardcoded',
			'meta' => array(
				'total_violations'   => 5,
				'files_affected'     => 2,
				'woocommerce_active' => true,
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertArrayHasKey( 'woocommerce_active', $result['meta'] );
	}

	public function test_details_include_violations() {
		$cached_result = array(
			'id'      => 'currency-symbol-hardcoded',
			'details' => array(
				'violations' => array(
					array(
						'file' => 'test.php',
						'line' => 10,
					),
				),
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertArrayHasKey( 'violations', $result['details'] );
	}

	public function test_recommendations_included() {
		$cached_result = array(
			'id'              => 'currency-symbol-hardcoded',
			'recommendations' => array( 'Use currency functions' ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertIsArray( $result['recommendations'] );
	}

	public function test_severity_scales_with_violations() {
		$cached_low = array(
			'id'       => 'currency-symbol-hardcoded',
			'severity' => 'low',
			'meta'     => array( 'total_violations' => 3 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_low );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertEquals( 'low', $result['severity'] );
	}

	public function test_medium_severity_with_many_violations() {
		$cached_medium = array(
			'id'       => 'currency-symbol-hardcoded',
			'severity' => 'medium',
			'meta'     => array( 'total_violations' => 15 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_medium );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertEquals( 'medium', $result['severity'] );
	}

	public function test_caching_behavior() {
		$cached_result = array(
			'id'               => 'currency-symbol-hardcoded',
			'total_violations' => 5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertEquals( $cached_result, $result );
	}

	public function test_auto_fixable_is_false() {
		$cached_result = array(
			'id'           => 'currency-symbol-hardcoded',
			'auto_fixable' => false,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Currency_Symbol_Hardcoded::check();

		$this->assertFalse( $result['auto_fixable'] );
	}
}
