<?php
/**
 * Tests for Geolocation Redirect Conflicts Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests
 */

use WPShadow\Diagnostics\Diagnostic_Geolocation_Redirect_Conflicts;
use WP_Mock\Tools\TestCase;

/**
 * Test Geolocation Redirect Conflicts Diagnostic
 */
class GeolocationRedirectConflictsTest extends TestCase {

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
	 * Test diagnostic passes when no multilingual plugin
	 */
	public function test_passes_when_no_multilingual_plugin() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->andReturn( false );
		WP_Mock::userFunction( 'function_exists' )->andReturn( false );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic passes when no redirect issues
	 */
	public function test_passes_when_no_issues() {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'TRP_Translate_Press' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'trp_settings' )->andReturn(
			array(
				'force-language-to-browser' => 'no',
			)
		);
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags auto-redirect without user choice respect
	 */
	public function test_flags_redirect_without_user_choice() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'geolocation-redirect-conflicts', $result['id'] );
		$this->assertArrayHasKey( 'issues_count', $result['meta'] );
		$this->assertGreaterThan( 0, $result['meta']['issues_count'] );
	}

	/**
	 * Test severity calculation
	 */
	public function test_severity_calculation() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		// With 3+ issues, severity should be high.
		if ( $result['meta']['issues_count'] >= 3 ) {
			$this->assertEquals( 'high', $result['severity'] );
			$this->assertGreaterThanOrEqual( 60, $result['threat_level'] );
		} else {
			$this->assertEquals( 'medium', $result['severity'] );
		}
	}

	/**
	 * Test diagnostic structure
	 */
	public function test_diagnostic_structure() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

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
	 * Test meta includes plugin info
	 */
	public function test_meta_includes_plugin_info() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertArrayHasKey( 'i18n_plugin', $result['meta'] );
		$this->assertEquals( 'wpml', $result['meta']['i18n_plugin'] );
		$this->assertArrayHasKey( 'has_auto_redirect', $result['meta'] );
		$this->assertArrayHasKey( 'respects_user_choice', $result['meta'] );
		$this->assertArrayHasKey( 'has_language_switcher', $result['meta'] );
		$this->assertArrayHasKey( 'stores_preference', $result['meta'] );
	}

	/**
	 * Test details populated
	 */
	public function test_details_populated() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertIsArray( $result['details'] );
		$this->assertNotEmpty( $result['details'] );
	}

	/**
	 * Test recommendations populated
	 */
	public function test_recommendations_populated() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertNotEmpty( $result['recommendations'] );
		$this->assertGreaterThanOrEqual( 3, count( $result['recommendations'] ) );
	}

	/**
	 * Test caching behavior
	 */
	public function test_caching_behavior() {
		$cached_result = array( 'id' => 'geolocation-redirect-conflicts' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		$this->assertEquals( $cached_result, $result );
	}

	/**
	 * Test threat level scales with issue count
	 */
	public function test_threat_level_scales() {
		$_COOKIE = array();

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'class_exists' )->with( 'SitePress' )->andReturn( true );
		WP_Mock::userFunction( 'get_option' )->with( 'icl_sitepress_settings' )->andReturn(
			array(
				'automatic_redirect' => 1,
				'remember_language'  => 0,
			)
		);
		WP_Mock::userFunction( 'is_active_widget' )->andReturn( false );
		WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html></html>' ) );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html></html>' );
		WP_Mock::userFunction( '__' )->andReturnFirstArg();
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Geolocation_Redirect_Conflicts::check();

		// Multiple issues should result in higher threat level.
		$this->assertGreaterThanOrEqual( 45, $result['threat_level'] );
	}
}
