<?php
/**
 * Tests for Video Files Local Hosting Diagnostic
 *
 * @package WPShadow\Tests
 */

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Video_Files_Local_Hosting;
use WP_Mock\Tools\TestCase;

/**
 * Test Video Files Local Hosting diagnostic
 */
class VideoFilesLocalHostingTest extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
		delete_transient( 'wpshadow_diagnostic_video_local' );
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_passes_when_few_local_videos() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array( 'baseurl' => 'http://example.com/wp-content/uploads' ) );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertNull( $result );
	}

	public function test_flags_many_local_videos() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( '/path/to/video.mp4' );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertIsArray( $result );
		$this->assertEquals( 'video-files-local-hosting', $result['id'] );
	}

	public function test_diagnostic_structure() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'severity', $result );
	}

	public function test_meta_includes_video_count() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'video_count', $result['meta'] );
		$this->assertEquals( 5, $result['meta']['video_count'] );
	}

	public function test_severity_scales_with_count() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 15; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertEquals( 'medium', $result['severity'] );
	}

	public function test_includes_details() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertArrayHasKey( 'details', $result );
		$this->assertIsArray( $result['details'] );
	}

	public function test_includes_recommendations() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertArrayHasKey( 'recommendations', $result );
		$this->assertIsArray( $result['recommendations'] );
	}

	public function test_respects_cache() {
		$cached_result = array( 'id' => 'test', 'cached' => true );
		\WP_Mock::userFunction( 'get_transient' )
			->with( 'wpshadow_diagnostic_video_local' )
			->andReturn( $cached_result );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertEquals( $cached_result, $result );
	}

	public function test_calculates_total_size() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertArrayHasKey( 'total_size_mb', $result['meta'] );
	}

	public function test_threat_level_increases_with_count() {
		global $wpdb;
		$wpdb = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';

		$videos = array();
		for ( $i = 0; $i < 20; $i++ ) {
			$video = new \stdClass();
			$video->ID = $i;
			$video->guid = 'http://example.com/wp-content/uploads/video' . $i . '.mp4';
			$video->post_mime_type = 'video/mp4';
			$videos[] = $video;
		}

		$wpdb->shouldReceive( 'prepare' )->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $videos );

		\WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		\WP_Mock::userFunction( 'set_transient' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_upload_dir' )->andReturn( array(
			'baseurl' => 'http://example.com/wp-content/uploads',
			'basedir' => '/var/www/wp-content/uploads'
		) );
		\WP_Mock::userFunction( 'get_attached_file' )->andReturn( false );
		\WP_Mock::userFunction( '__' )->andReturnUsing( function( $text ) { return $text; } );
		\WP_Mock::userFunction( 'number_format' )->andReturnUsing( function( $num, $dec ) { return (string) round( $num, $dec ); } );

		$result = Diagnostic_Video_Files_Local_Hosting::check();
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertLessThanOrEqual( 70, $result['threat_level'] );
	}
}
