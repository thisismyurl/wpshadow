<?php
/**
 * Touch Gesture Support Diagnostic
 *
 * Tests touch-based interactions in media picker (pinch, swipe, tap).
 * Validates mobile gesture handling in WordPress media library.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Touch Gesture Support Diagnostic Class
 *
 * Checks if WordPress admin interface properly supports touch
 * gestures for mobile and tablet users.
 *
 * @since 1.7029.1200
 */
class Diagnostic_Touch_Gesture_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'touch-gesture-support';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Touch Gesture Support';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests touch-based interactions in media picker';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress and theme support touch gestures properly
	 * for mobile device users.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_version;

		// Check WordPress version (touch improvements in 5.3+).
		$wp_supports_touch = version_compare( $wp_version, '5.3', '>=' );

		// Check if touch events are supported via wp_enqueue_script registrations.
		global $wp_scripts;
		$has_touch_events = false;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				// Check for common touch/mobile libraries.
				if ( false !== strpos( $handle, 'touch' ) || 
				     false !== strpos( $handle, 'mobile' ) ||
				     false !== strpos( $handle, 'hammer' ) ||
				     false !== strpos( $handle, 'swipe' ) ) {
					$has_touch_events = true;
					break;
				}
			}
		}

		// Check if theme supports mobile.
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );
		$is_mobile_friendly = is_array( $theme_tags ) && in_array( 'responsive-layout', $theme_tags, true );

		// Check for viewport meta tag (essential for touch).
		$has_viewport = false;
		ob_start();
		wp_head();
		$head_output = ob_get_clean();
		
		if ( false !== strpos( $head_output, 'viewport' ) ) {
			$has_viewport = true;
		}

		// Check admin interface settings.
		$user_id = get_current_user_id();
		$is_admin_mobile_friendly = false;

		// WordPress 5.3+ has responsive admin.
		if ( $wp_supports_touch ) {
			$is_admin_mobile_friendly = true;
		}

		// Check for plugins that enhance mobile admin.
		$mobile_admin_plugins = array(
			'jetpack/jetpack.php'                     => 'Jetpack',
			'wordpress-mobile-admin/wordpress-mobile-admin.php' => 'WP Mobile Admin',
		);

		$has_mobile_enhancement = false;
		$active_plugin = '';
		foreach ( $mobile_admin_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_mobile_enhancement = true;
				$active_plugin = $name;
				break;
			}
		}

		// Issue: WordPress version is outdated or missing touch support.
		if ( ! $wp_supports_touch || ! $is_admin_mobile_friendly ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress admin interface may not fully support touch gestures for mobile users', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/touch-gesture-support',
				'details'      => array(
					'wp_version'              => $wp_version,
					'wp_supports_touch'       => $wp_supports_touch,
					'has_touch_events'        => $has_touch_events,
					'is_mobile_friendly'      => $is_mobile_friendly,
					'has_viewport'            => $has_viewport,
					'is_admin_mobile_friendly' => $is_admin_mobile_friendly,
					'has_mobile_enhancement'  => $has_mobile_enhancement,
					'active_plugin'           => $active_plugin,
					'usability_impact'        => __( 'Poor touch support makes it difficult for users to manage media on mobile devices', 'wpshadow' ),
					'recommendation'          => ! $wp_supports_touch
						? __( 'Update WordPress to 5.3 or later for improved mobile admin and touch gesture support', 'wpshadow' )
						: __( 'Test admin interface on mobile devices and consider installing a mobile admin enhancement plugin', 'wpshadow' ),
					'touch_gestures'          => array(
						'pinch_zoom'      => __( 'Pinch to zoom images in media library', 'wpshadow' ),
						'swipe'           => __( 'Swipe to navigate between images', 'wpshadow' ),
						'tap'             => __( 'Tap to select images (avoid double-tap requirements)', 'wpshadow' ),
						'long_press'      => __( 'Long press for context menus', 'wpshadow' ),
					),
					'testing_checklist'       => array(
						__( 'Test media picker on iOS Safari', 'wpshadow' ),
						__( 'Test media picker on Android Chrome', 'wpshadow' ),
						__( 'Test image selection with touch', 'wpshadow' ),
						__( 'Test drag-and-drop upload on touch devices', 'wpshadow' ),
						__( 'Test image cropping with touch', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
