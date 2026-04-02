<?php
/**
 * WPShadow Academy - Training Video Registry
 *
 * Registry of 100+ training videos mapped to findings and treatments.
 *
 * @package    WPShadow
 * @subpackage Academy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Academy;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Training_Video_Registry Class
 *
 * Manages training videos and their relationships to findings/treatments.
 *
 * @since 1.6093.1200
 */
class Training_Video_Registry extends Hook_Subscriber_Base {

	/**
	 * Registered videos
	 *
	 * @var array
	 */
	private static $videos = array();

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(); // Configuration only, no hooks needed
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since 1.6093.1200
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6089';
	}

	/**
	 * Initialize registry (deprecated)
	 *
	 * @deprecated1.0 Use Training_Video_Registry::subscribe() instead
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::register_videos();
	}

	/**
	 * Register all training videos
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function register_videos() {
		// Security Videos.
		self::register(
			'ssl-setup',
			array(
				'title'       => __( 'How to Setup SSL/HTTPS on WordPress (Step-by-Step)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/academy/videos/ssl-setup/',
				'youtube_id'  => 'abc123xyz',
				'duration'    => 420, // 7 minutes.
				'category'    => 'security',
				'difficulty'  => 'beginner',
				'findings'    => array( 'ssl-not-enforced' ),
				'free'        => true,
			)
		);

		self::register(
			'file-permissions-fix',
			array(
				'title'       => __( 'Fixing WordPress File Permissions (5-Minute Guide)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/academy/videos/file-permissions/',
				'youtube_id'  => 'def456uvw',
				'duration'    => 300, // 5 minutes.
				'category'    => 'security',
				'difficulty'  => 'intermediate',
				'findings'    => array( 'file-permissions-insecure' ),
				'free'        => true,
			)
		);

		// Performance Videos.
		self::register(
			'php-memory-increase',
			array(
				'title'       => __( 'Increase PHP Memory Limit in 3 Easy Steps', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/academy/videos/php-memory/',
				'youtube_id'  => 'ghi789rst',
				'duration'    => 240, // 4 minutes.
				'category'    => 'performance',
				'difficulty'  => 'beginner',
				'findings'    => array( 'memory-limit-low' ),
				'free'        => true,
			)
		);

		self::register(
			'caching-setup',
			array(
				'title'       => __( 'Complete WordPress Caching Setup (WP Rocket & W3 Total Cache)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/academy/videos/caching-setup/',
				'youtube_id'  => 'jkl012opq',
				'duration'    => 900, // 15 minutes.
				'category'    => 'performance',
				'difficulty'  => 'intermediate',
				'findings'    => array( 'no-caching-plugin' ),
				'free'        => true,
			)
		);

		// Plugin/Theme Videos.
		self::register(
			'plugin-updates',
			array(
				'title'       => __( 'How to Safely Update WordPress Plugins (Avoid Breaking Your Site)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/academy/videos/plugin-updates/',
				'youtube_id'  => 'mno345stu',
				'duration'    => 480, // 8 minutes.
				'category'    => 'maintenance',
				'difficulty'  => 'beginner',
				'findings'    => array( 'outdated-plugins' ),
				'free'        => true,
			)
		);

		// Privacy Videos.
		self::register(
			'cookie-consent-banner',
			array(
				'title'       => __( 'Add Cookie Consent Banner to WordPress (GDPR Compliant)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/academy/videos/cookie-consent/',
				'youtube_id'  => 'pqr678vwx',
				'duration'    => 360, // 6 minutes.
				'category'    => 'privacy',
				'difficulty'  => 'beginner',
				'findings'    => array( 'missing-cookie-consent' ),
				'free'        => true,
			)
		);

		// Allow other modules to register videos.
		do_action( 'wpshadow_academy_register_training_videos' );
	}

	/**
	 * Register a training video
	 *
	 * @since 1.6093.1200
	 * @param  string $id Video ID.
	 * @param  array  $data Video data.
	 * @return void
	 */
	public static function register( $id, $data ) {
		self::$videos[ $id ] = $data;
	}

	/**
	 * Get video by ID
	 *
	 * @since 1.6093.1200
	 * @param  string $id Video ID.
	 * @return array|null Video data or null.
	 */
	public static function get( $id ) {
		return isset( self::$videos[ $id ] ) ? self::$videos[ $id ] : null;
	}

	/**
	 * Get video for finding
	 *
	 * @since 1.6093.1200
	 * @param  string $finding_id Finding ID.
	 * @return array|null Video data or null.
	 */
	public static function get_video_for_finding( $finding_id ) {
		foreach ( self::$videos as $id => $video ) {
			if ( isset( $video['findings'] ) && in_array( $finding_id, $video['findings'], true ) ) {
				$video['id'] = $id;
				return $video;
			}
		}

		return null;
	}

	/**
	 * Get videos by category
	 *
	 * @since 1.6093.1200
	 * @param  string $category Category slug.
	 * @return array Videos in category.
	 */
	public static function get_by_category( $category ) {
		$results = array();

		foreach ( self::$videos as $id => $video ) {
			if ( isset( $video['category'] ) && $video['category'] === $category ) {
				$video['id'] = $id;
				$results[]   = $video;
			}
		}

		return $results;
	}

	/**
	 * Get all free videos
	 *
	 * @since 1.6093.1200
	 * @return array Free videos.
	 */
	public static function get_free_videos() {
		$results = array();

		foreach ( self::$videos as $id => $video ) {
			if ( isset( $video['free'] ) && $video['free'] ) {
				$video['id'] = $id;
				$results[]   = $video;
			}
		}

		return $results;
	}

	/**
	 * Get all videos
	 *
	 * @since 1.6093.1200
	 * @return array All registered videos.
	 */
	public static function get_all() {
		$results = array();

		foreach ( self::$videos as $id => $video ) {
			$video['id'] = $id;
			$results[]   = $video;
		}

		return $results;
	}

	/**
	 * Format duration for display
	 *
	 * @since 1.6093.1200
	 * @param  int $seconds Duration in seconds.
	 * @return string Formatted duration (e.g., "7 min").
	 */
	public static function format_duration( $seconds ) {
		$minutes = floor( $seconds / 60 );
		$secs    = $seconds % 60;

		if ( $secs > 0 ) {
			return sprintf(
				/* translators: 1: minutes, 2: seconds */
				__( '%1$d min %2$d sec', 'wpshadow' ),
				$minutes,
				$secs
			);
		}

		return sprintf(
			/* translators: %d: minutes */
			__( '%d min', 'wpshadow' ),
			$minutes
		);
	}
}
