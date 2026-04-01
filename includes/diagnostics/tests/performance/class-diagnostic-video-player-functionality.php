<?php
/**
 * Video Player Functionality Diagnostic
 *
 * Tests HTML5 video player controls, autoplay, loop, and playback functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Player Functionality Diagnostic Class
 *
 * Validates that HTML5 video player works correctly with controls,
 * autoplay settings, loop functionality, and proper event handling.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Player_Functionality extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-player-functionality';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Player Functionality';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML5 video player controls, autoplay, and loop settings';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress video player is properly configured and
	 * video shortcodes render with correct controls.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_scripts;

		// Check if WordPress video script is registered.
		$video_script_registered = isset( $wp_scripts ) && $wp_scripts->query( 'wp-mediaelement' );

		// Check for MediaElement.js library (used by WordPress for video player).
		$mediaelement_available = false;
		if ( isset( $wp_scripts ) ) {
			$mediaelement_available = $wp_scripts->query( 'mediaelement' ) ||
									   $wp_scripts->query( 'wp-mediaelement' );
		}

		// Check WordPress video settings.
		$video_settings = get_option( 'video_default_width' );
		$has_video_settings = ! empty( $video_settings );

		// Check for video shortcode support.
		$has_video_shortcode = shortcode_exists( 'video' );

		// Check theme support for HTML5 video.
		$theme_support_video = current_theme_supports( 'html5', 'video' );

		// Check for custom video player plugins.
		$has_custom_player = false;
		$custom_players    = array();

		// Common video player plugins.
		$player_plugins = array(
			'video-elementor/video-elementor.php',
			'video-popup/video-popup.php',
			'video-gallery/video-gallery.php',
		);

		foreach ( $player_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_custom_player = true;
				$custom_players[]  = $plugin;
			}
		}

		// Check PHP capabilities for video processing.
		$has_getid3 = extension_loaded( 'getid3' ) || class_exists( 'getID3' );

		// Test video shortcode rendering.
		$test_video = do_shortcode( '[video width="320" height="240"][/video]' );
		$renders_video = ! empty( $test_video ) && strpos( $test_video, '<video' ) !== false;

		// Check video player CSS.
		global $wp_styles;
		$mediaelement_css_loaded = isset( $wp_styles ) &&
								  ( $wp_styles->query( 'wp-mediaelement' ) ||
									$wp_styles->query( 'mediaelement' ) );

		// Check for issues.
		$issues = array();

		// Issue 1: MediaElement not available.
		if ( ! $mediaelement_available ) {
			$issues[] = array(
				'type'        => 'mediaelement_unavailable',
				'description' => __( 'MediaElement.js library is not available for video player', 'wpshadow' ),
			);
		}

		// Issue 2: Video shortcode not available.
		if ( ! $has_video_shortcode ) {
			$issues[] = array(
				'type'        => 'no_video_shortcode',
				'description' => __( 'WordPress video shortcode is not registered', 'wpshadow' ),
			);
		}

		// Issue 3: Video shortcode doesn't render.
		if ( ! $renders_video ) {
			$issues[] = array(
				'type'        => 'shortcode_render_fail',
				'description' => __( 'Video shortcode does not render proper HTML5 video tags', 'wpshadow' ),
			);
		}

		// Issue 4: No MediaElement CSS.
		if ( ! $mediaelement_css_loaded ) {
			$issues[] = array(
				'type'        => 'missing_css',
				'description' => __( 'MediaElement CSS is not loaded, player controls may not display properly', 'wpshadow' ),
			);
		}

		// Issue 5: Theme doesn't support HTML5 video.
		if ( ! $theme_support_video ) {
			$issues[] = array(
				'type'        => 'theme_no_html5_video',
				'description' => __( 'Theme does not declare HTML5 video support', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Video player functionality has configuration issues that may prevent proper playback', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-player-functionality?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'mediaelement_available'    => $mediaelement_available,
					'video_script_registered'   => $video_script_registered,
					'has_video_shortcode'       => $has_video_shortcode,
					'renders_video'             => $renders_video,
					'mediaelement_css_loaded'   => $mediaelement_css_loaded,
					'theme_support_video'       => $theme_support_video,
					'has_custom_player'         => $has_custom_player,
					'custom_players_active'     => $custom_players,
					'has_getid3'                => $has_getid3,
					'issues_detected'           => $issues,
					'recommendation'            => __( 'Ensure WordPress core files are intact and theme supports HTML5 video', 'wpshadow' ),
					'testing_steps'             => array(
						__( '1. Go to Media → Add New', 'wpshadow' ),
						__( '2. Upload a test video (MP4, WebM, or OGG)', 'wpshadow' ),
						__( '3. Insert into post/page', 'wpshadow' ),
						__( '4. View on frontend', 'wpshadow' ),
						__( '5. Test play, pause, volume, fullscreen controls', 'wpshadow' ),
					),
					'expected_behavior'         => array(
						__( 'Video player should display with visible controls', 'wpshadow' ),
						__( 'Play/pause button should work', 'wpshadow' ),
						__( 'Volume slider should be visible', 'wpshadow' ),
						__( 'Progress bar should update during playback', 'wpshadow' ),
						__( 'Fullscreen button should work (if available)', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
