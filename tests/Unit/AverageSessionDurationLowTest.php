<?php
/**
 * Tests for Average Session Duration Low Diagnostic
 *
 * @package WPShadow\Tests
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Average_Session_Duration_Low;
use WP_Mock\Tools\TestCase;

/**
 * Test Average Session Duration diagnostic
 */
class AverageSessionDurationLowTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
		delete_transient( 'wpshadow_diagnostic_session_duration' );
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_when_no_analytics_data() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertNull( $result );
	}

	public function test_passes_when_duration_above_2_minutes() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 150 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertNull( $result );
	}

	public function test_flags_duration_below_1_minute() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 45 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
	}

	public function test_flags_duration_1_to_2_minutes_as_medium() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 90 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertIsArray( $result );
		$this->assertEquals( 'medium', $result['severity'] );
	}

	public function test_diagnostic_structure() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 40 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertEquals( 'average-session-duration-low', $result['id'] );
	}

	public function test_meta_includes_duration_data() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 50 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'average_duration_seconds', $result['meta'] );
		$this->assertArrayHasKey( 'average_duration_minutes', $result['meta'] );
	}

	public function test_includes_details_array() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 55 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertArrayHasKey( 'details', $result );
		$this->assertIsArray( $result['details'] );
		$this->assertNotEmpty( $result['details'] );
	}

	public function test_includes_recommendations() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 50 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertIsArray( $result['recommendations'] );
		$this->assertNotEmpty( $result['recommendations'] );
	}

	public function test_respects_cache() {
		$cached_result = array( 'id' => 'test', 'cached' => true );
		\WP_Mock::userFunction( 'get_transient' )
			->with( 'wpshadow_diagnostic_session_duration' )
			->andReturn( $cached_result );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertEquals( $cached_result, $result );
	}

	public function test_threat_level_scales_with_duration() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )
			->with( 'wpshadow_analytics_session_duration' )
			->andReturn( array( 'average_duration' => 30 ) );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Average_Session_Duration_Low::check();
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertGreaterThanOrEqual( 50, $result['threat_level'] );
	}
}
