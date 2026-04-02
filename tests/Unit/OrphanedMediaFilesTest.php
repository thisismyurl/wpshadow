<?php
/**
 * Tests for Orphaned Media Files Diagnostic
 *
 * @package WPShadow\Tests
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Orphaned_Media_Files;
use WP_Mock\Tools\TestCase;

class OrphanedMediaFilesTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
		delete_transient( 'wpshadow_diagnostic_orphaned_media' );
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_when_orphan_count_low() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertNull( $result );
	}

	public function test_flags_high_orphan_count() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertIsArray( $result );
		$this->assertEquals( 'orphaned-media-files', $result['id'] );
	}

	public function test_diagnostic_structure() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
	}

	public function test_meta_includes_orphan_stats() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'orphan_count', $result['meta'] );
	}

	public function test_severity_scales_with_count() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 600; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertEquals( 'medium', $result['severity'] );
	}

	public function test_includes_details() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertArrayHasKey( 'details', $result );
		$this->assertIsArray( $result['details'] );
	}

	public function test_includes_recommendations() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertIsArray( $result['recommendations'] );
	}

	public function test_respects_cache() {
		$cached_result = array( 'id' => 'test', 'cached' => true );
		\WP_Mock::userFunction( 'get_transient' )
			->with( 'wpshadow_diagnostic_orphaned_media' )
			->andReturn( $cached_result );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertEquals( $cached_result, $result );
	}

	public function test_calculates_storage_waste() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertArrayHasKey( 'total_size_mb', $result['meta'] );
	}

	public function test_threat_level_scales() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$media = array();
		for ( $i = 0; $i < 150; $i++ ) {
			$item = new \stdClass();
			$item->ID = $i;
			$item->guid = 'http://example.com/wp-content/uploads/image' . $i . '.jpg';
			$media[] = $item;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $media );
		$wpdb->shouldReceive( 'get_var' )->andReturn( null );
		$wpdb->shouldReceive( 'esc_like' )->andReturnUsing( function( $text ) { return $text; } );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_get_attachment_url' )->andReturn( 'http://example.com/image.jpg' );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Orphaned_Media_Files::check();
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertLessThanOrEqual( 60, $result['threat_level'] );
	}
}
