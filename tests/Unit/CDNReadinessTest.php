<?php
/**
 * Tests for CDN Readiness Check Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_CDN_Readiness;
use WP_Mock\Tools\TestCase;

/**
 * CDN Readiness Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class CDNReadinessTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic passes when CDN ready
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_when_cdn_ready(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => true,
				'issues'                 => array(),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags protocol-relative URLs
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_protocol_relative_urls(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Site uses protocol-relative URLs (// format)' ),
				'protocol_relative_urls' => true,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => '//example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issues that may prevent CDN integration',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'cdn-readiness', $result['id'] );
		$this->assertTrue( $result['meta']['has_protocol_relative_urls'] ?? false );
	}

	/**
	 * Test diagnostic flags absolute URLs
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_absolute_urls(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( '15 posts contain hardcoded absolute URLs' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 15,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issues that may prevent CDN integration',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['meta']['absolute_url_count'] );
	}

	/**
	 * Test diagnostic flags hardcoded assets
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_hardcoded_assets(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( '3 assets found hardcoded in templates' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 3,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Found 1 issues that may prevent CDN integration',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 3, $result['meta']['hardcoded_assets'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test issue' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 5,
				'hardcoded_assets'       => 2,
				'dynamic_assets'         => true,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Issues found',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
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

		$this->assertEquals( 'cdn-readiness', $result['id'] );
		$this->assertEquals( 'performance', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test meta includes CDN readiness status
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_meta_includes_cdn_status(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => true,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'cdn_ready', $result['meta'] );
		$this->assertArrayHasKey( 'issue_count', $result['meta'] );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
	}

	/**
	 * Test details include why CDN matters
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_why_matters(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'why_cdn_matters', $result['details'] );
		$this->assertNotEmpty( $result['details']['why_cdn_matters'] );
	}

	/**
	 * Test details include recommended steps
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_recommended_steps(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommended_steps', $result['details'] );
		$this->assertNotEmpty( $result['details']['recommended_steps'] );
	}

	/**
	 * Test details include CDN providers
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_cdn_providers(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'cdn_providers', $result['details'] );
		$this->assertArrayHasKey( 'cloudflare', $result['details']['cdn_providers'] );
	}

	/**
	 * Test threat level increases with issues
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_threat_level_increases_with_issues(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Issue 1', 'Issue 2', 'Issue 3' ),
				'protocol_relative_urls' => true,
				'absolute_url_count'     => 20,
				'hardcoded_assets'       => 5,
				'dynamic_assets'         => true,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 20, $result['threat_level'] );
		$this->assertLessThanOrEqual( 25, $result['threat_level'] );
	}

	/**
	 * Test performance impact calculation
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_performance_impact_calculation(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'performance_impact', $result['meta'] );
		$this->assertStringContainsString( 'CDN', $result['meta']['performance_impact'] );
	}

	/**
	 * Test details include expected benefits
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_details_include_expected_benefits(): void {
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'cdn_ready'              => false,
				'issues'                 => array( 'Test' ),
				'protocol_relative_urls' => false,
				'absolute_url_count'     => 0,
				'hardcoded_assets'       => 0,
				'dynamic_assets'         => false,
				'site_url'               => 'https://example.com',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'Test',
		) );

		$result = Diagnostic_CDN_Readiness::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'expected_benefits', $result['details'] );
		$this->assertArrayHasKey( 'lcp', $result['details']['expected_benefits'] );
	}
}
