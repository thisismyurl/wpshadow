<?php
/**
 * Tests for Internal Link Anchor Text Diversity Diagnostic
 *
 * @package WPShadow\Tests
 * @since 1.6093.1200
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Internal_Link_Anchor_Diversity;
use WP_Mock\Tools\TestCase;

/**
 * Internal Link Anchor Diversity Test Class
 *
 * @since 1.6093.1200
 */
class InternalLinkAnchorDiversityTest extends TestCase {

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
	 * Test diagnostic passes with diverse anchors
	 *
	 * @return void
	 */
	public function test_passes_with_diverse_anchors() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => '<html><a href="/page1">Click here</a><a href="/page2">Learn more</a></html>' ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '<html><a href="/page1">Click here</a><a href="/page2">Learn more</a></html>' );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags over-optimization
	 *
	 * @return void
	 */
	public function test_flags_over_optimization() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		
		$html = '<html>';
		for ( $i = 0; $i < 20; $i++ ) {
			$html .= '<a href="/page' . $i . '">best keyword</a>';
		}
		$html .= '</html>';
		
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'internal-link-anchor-diversity', $result['id'] );
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
		
		$html = '<html>';
		for ( $i = 0; $i < 20; $i++ ) {
			$html .= '<a href="/page' . $i . '">keyword</a>';
		}
		$html .= '</html>';
		
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'recommendations', $result );
	}

	/**
	 * Test meta includes anchor statistics
	 *
	 * @return void
	 */
	public function test_meta_includes_anchor_stats() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		
		$html = str_repeat( '<a href="/page">keyword</a>', 15 );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertArrayHasKey( 'total_links', $result['meta'] );
		$this->assertArrayHasKey( 'unique_anchors', $result['meta'] );
		$this->assertArrayHasKey( 'exact_match_percentage', $result['meta'] );
		$this->assertArrayHasKey( 'diversity_score', $result['meta'] );
	}

	/**
	 * Test details include top anchors
	 *
	 * @return void
	 */
	public function test_details_include_top_anchors() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		
		$html = str_repeat( '<a href="/page">keyword</a>', 15 );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertArrayHasKey( 'top_anchors', $result['details'] );
		$this->assertIsArray( $result['details']['top_anchors'] );
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
		
		$html = str_repeat( '<a href="/page">keyword</a>', 15 );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertIsArray( $result['recommendations'] );
		$this->assertGreaterThan( 0, count( $result['recommendations'] ) );
	}

	/**
	 * Test severity scales with over-optimization
	 *
	 * @return void
	 */
	public function test_severity_scales_with_optimization() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		
		$html = str_repeat( '<a href="/page">keyword</a>', 15 );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertContains( $result['severity'], array( 'low', 'medium', 'high' ) );
		$this->assertIsInt( $result['threat_level'] );
	}

	/**
	 * Test high severity with critical over-optimization
	 *
	 * @return void
	 */
	public function test_high_severity_with_critical_optimization() {
		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'home_url' )->andReturn( 'https://example.com' );
		\WP_Mock::userFunction( 'get_posts' )->andReturn( array() );
		
		$html = str_repeat( '<a href="/page">exact match keyword</a>', 50 );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertEquals( 'high', $result['severity'] );
	}

	/**
	 * Test caching behavior
	 *
	 * @return void
	 */
	public function test_caching_behavior() {
		$cached_result = array(
			'id'                     => 'internal-link-anchor-diversity',
			'exact_match_percentage' => 85.0,
		);

		\WP_Mock::userFunction( 'get_transient' )->andReturn( $cached_result );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

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
		
		$html = str_repeat( '<a href="/page">keyword</a>', 15 );
		\WP_Mock::userFunction( 'wp_remote_get' )->andReturn( array( 'body' => $html ) );
		\WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $html );
		\WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing(
			function( $text ) {
				return strip_tags( $text );
			}
		);
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( '__' )->andReturnArg( 0 );

		$result = Diagnostic_Internal_Link_Anchor_Diversity::check();

		$this->assertFalse( $result['auto_fixable'] );
	}
}
