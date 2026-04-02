<?php
/**
 * Tests for Date Format Not Localized Diagnostic
 *
 * @package WPShadow\Tests
 * @since 1.6093.1200
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Date_Format_Not_Localized;
use WP_Mock\Tools\TestCase;

/**
 * Date Format Not Localized Test Class
 *
 * @since 1.6093.1200
 */
class DateFormatNotLocalizedTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_with_localized_dates() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_stylesheet_directory' )->andReturn( '/tmp/theme' );
		\WP_Mock::userFunction( 'get_option' )->andReturn( array() );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertNull( $result );
	}

	public function test_flags_hardcoded_dates() {
		$cached_result = array(
			'id'               => 'date-format-not-localized',
			'total_violations' => 5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'date-format-not-localized', $result['id'] );
	}

	public function test_finding_structure_valid() {
		$cached_result = array(
			'id'           => 'date-format-not-localized',
			'title'        => 'Date Format Not Localized',
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

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
	}

	public function test_meta_includes_date_format() {
		$cached_result = array(
			'id'   => 'date-format-not-localized',
			'meta' => array(
				'total_violations' => 5,
				'files_affected'   => 2,
				'date_format'      => 'Y-m-d',
				'time_format'      => 'H:i:s',
			),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertArrayHasKey( 'date_format', $result['meta'] );
		$this->assertArrayHasKey( 'time_format', $result['meta'] );
	}

	public function test_details_include_violations() {
		$cached_result = array(
			'id'      => 'date-format-not-localized',
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

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertArrayHasKey( 'violations', $result['details'] );
	}

	public function test_recommendations_included() {
		$cached_result = array(
			'id'              => 'date-format-not-localized',
			'recommendations' => array( 'Use date_i18n()' ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertIsArray( $result['recommendations'] );
	}

	public function test_severity_scales_with_violations() {
		$cached_low = array(
			'id'       => 'date-format-not-localized',
			'severity' => 'low',
			'meta'     => array( 'total_violations' => 3 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_low );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertEquals( 'low', $result['severity'] );
	}

	public function test_medium_severity_with_many_violations() {
		$cached_medium = array(
			'id'       => 'date-format-not-localized',
			'severity' => 'medium',
			'meta'     => array( 'total_violations' => 15 ),
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_medium );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertEquals( 'medium', $result['severity'] );
	}

	public function test_caching_behavior() {
		$cached_result = array(
			'id'               => 'date-format-not-localized',
			'total_violations' => 5,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertEquals( $cached_result, $result );
	}

	public function test_auto_fixable_is_false() {
		$cached_result = array(
			'id'           => 'date-format-not-localized',
			'auto_fixable' => false,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Date_Format_Not_Localized::check();

		$this->assertFalse( $result['auto_fixable'] );
	}
}
