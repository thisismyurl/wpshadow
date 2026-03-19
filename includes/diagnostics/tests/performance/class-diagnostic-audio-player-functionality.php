<?php
/**
 * Audio Player Functionality Diagnostic
 *
 * Tests HTML5 audio player and WordPress audio shortcode functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audio Player Functionality Diagnostic Class
 *
 * Validates that HTML5 audio player works correctly with proper controls,
 * and WordPress audio shortcode renders with correct attributes.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Audio_Player_Functionality extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'audio-player-functionality';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Audio Player Functionality';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML5 audio player and WordPress audio shortcode functionality';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress audio player is properly configured and
	 * audio shortcodes render with correct controls.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_scripts;

		// Check if WordPress audio script is registered.
		$audio_script_registered = isset( $wp_scripts ) && $wp_scripts->query( 'wp-mediaelement' );

		// Check for MediaElement.js library (used by WordPress for audio player).
		$mediaelement_available = false;
		if ( isset( $wp_scripts ) ) {
			$mediaelement_available = $wp_scripts->query( 'mediaelement' ) || 
									   $wp_scripts->query( 'wp-mediaelement' );
		}

		// Check for audio shortcode support.
		$has_audio_shortcode = shortcode_exists( 'audio' );

		// Check theme support for HTML5 audio.
		$theme_support_audio = current_theme_supports( 'html5', 'audio' );

		// Check for custom audio player plugins.
		$has_custom_player = false;
		$custom_players    = array();

		// Common audio player plugins.
		$player_plugins = array(
			'podcast-player/podcast-player.php',
			'audio-player-for-elementor/audio-player.php',
			'compact-wp-audio-player/compact-audio-player.php',
		);

		foreach ( $player_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_custom_player = true;
				$custom_players[]  = $plugin;
			}
		}

		// Test audio shortcode rendering.
		$test_audio = do_shortcode( '[audio][/audio]' );
		$renders_audio = ! empty( $test_audio ) && strpos( $test_audio, '<audio' ) !== false;

		// Check audio player CSS.
		global $wp_styles;
		$mediaelement_css_loaded = isset( $wp_styles ) && 
								  ( $wp_styles->query( 'wp-mediaelement' ) || 
									$wp_styles->query( 'mediaelement' ) );

		// Check for recent audio uploads.
		global $wpdb;
		$recent_audio = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			 WHERE post_type = 'attachment' 
			 AND post_mime_type LIKE 'audio/%'
			 AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		// Check for podcast support.
		$has_podcast_plugin = is_plugin_active( 'seriously-simple-podcasting/seriously-simple-podcasting.php' ) ||
							  is_plugin_active( 'podlove-podcasting-plugin-for-wordpress/podlove.php' ) ||
							  is_plugin_active( 'powerpress/powerpress.php' );

		// Check for issues.
		$issues = array();

		// Issue 1: MediaElement not available.
		if ( ! $mediaelement_available ) {
			$issues[] = array(
				'type'        => 'mediaelement_unavailable',
				'description' => __( 'MediaElement.js library is not available for audio player', 'wpshadow' ),
			);
		}

		// Issue 2: Audio shortcode not available.
		if ( ! $has_audio_shortcode ) {
			$issues[] = array(
				'type'        => 'no_audio_shortcode',
				'description' => __( 'WordPress audio shortcode is not registered', 'wpshadow' ),
			);
		}

		// Issue 3: Audio shortcode doesn't render.
		if ( ! $renders_audio ) {
			$issues[] = array(
				'type'        => 'shortcode_render_fail',
				'description' => __( 'Audio shortcode does not render proper HTML5 audio tags', 'wpshadow' ),
			);
		}

		// Issue 4: No MediaElement CSS.
		if ( ! $mediaelement_css_loaded ) {
			$issues[] = array(
				'type'        => 'missing_css',
				'description' => __( 'MediaElement CSS is not loaded, player controls may not display properly', 'wpshadow' ),
			);
		}

		// Issue 5: Theme doesn't support HTML5 audio.
		if ( ! $theme_support_audio ) {
			$issues[] = array(
				'type'        => 'theme_no_html5_audio',
				'description' => __( 'Theme does not declare HTML5 audio support', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Audio player functionality has configuration issues that may prevent proper playback', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/audio-player-functionality',
				'details'      => array(
					'mediaelement_available'  => $mediaelement_available,
					'audio_script_registered' => $audio_script_registered,
					'has_audio_shortcode'     => $has_audio_shortcode,
					'renders_audio'           => $renders_audio,
					'mediaelement_css_loaded' => $mediaelement_css_loaded,
					'theme_support_audio'     => $theme_support_audio,
					'has_custom_player'       => $has_custom_player,
					'custom_players_active'   => $custom_players,
					'recent_audio_uploads'    => absint( $recent_audio ),
					'has_podcast_plugin'      => $has_podcast_plugin,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Ensure WordPress core files are intact and theme supports HTML5 audio', 'wpshadow' ),
					'testing_steps'           => array(
						__( '1. Go to Media → Add New', 'wpshadow' ),
						__( '2. Upload a test audio file (MP3, OGG, or WAV)', 'wpshadow' ),
						__( '3. Insert into post/page using audio shortcode', 'wpshadow' ),
						__( '4. View on frontend', 'wpshadow' ),
						__( '5. Test play, pause, volume, and progress bar controls', 'wpshadow' ),
					),
					'expected_behavior'       => array(
						__( 'Audio player should display with visible controls', 'wpshadow' ),
						__( 'Play/pause button should work', 'wpshadow' ),
						__( 'Volume slider should be visible and functional', 'wpshadow' ),
						__( 'Progress bar should update during playback', 'wpshadow' ),
						__( 'Duration should display correctly', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
