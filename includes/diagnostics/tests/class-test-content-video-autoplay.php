<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Video_Autoplay extends Diagnostic_Base {


	protected static $slug        = 'test-content-video-autoplay';
	protected static $title       = 'Video Autoplay Test';
	protected static $description = 'Tests for autoplay on embedded videos.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$body = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		if ( $body === false ) {
			return null;
		}

		$autoplay_videos = preg_match_all( '/(?:autoplay|auto-play)[=\s]/i', $body, $autoplay_matches );

		// Only trigger if at least one video exists and has autoplay
		$has_video = preg_match( '/<(?:iframe|video)[^>]+/i', $body );

		if ( $has_video && $autoplay_videos > 0 ) {
			return array(
				'id'            => 'content-video-autoplay',
				'title'         => 'Video Autoplay Detected',
				'description'   => sprintf( '%d video(s) set to autoplay. Autoplay is poor UX, wastes bandwidth, and harms accessibility.', $autoplay_videos )
				'kb_link' => 'https://wpshadow.com/kb/video-autoplay/',
				'training_link' => 'https://wpshadow.com/training/multimedia-best-practices/',
				'auto_fixable'  => false,
				'threat_level'  => 35,
				'module'        => 'Content Quality',
				'priority'      => 3,
				'meta'          => array( 'autoplay_count' => $autoplay_videos ),
			);
		}

		return null;
	}

	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);
		return is_wp_error( $response ) ? false : wp_remote_retrieve_body( $response );
	}

	public static function get_name(): string {
		return __( 'Video Autoplay', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks if embedded videos are set to autoplay.', 'wpshadow' );
	}
}
