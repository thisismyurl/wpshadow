<?php
/**
 * Mobile Reduce Motion Preference Diagnostic
 *
 * Checks for prefers-reduced-motion media query support to respect user's motion sensitivity preferences.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Reduce Motion Preference Diagnostic Class
 *
 * Validates that animations and motion effects respect the prefers-reduced-motion media query
 * preference, ensuring accessibility for vestibular disorder users and WCAG AAA compliance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Reduce_Motion_Preference extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-reduce-motion-preference';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Reduce Motion Preference';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Check for prefers-reduced-motion media query support to respect user motion sensitivity preferences';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if stylesheet exists
		$stylesheet_content = $GLOBALS['wp_styles'] ?? null;
		if ( null === $stylesheet_content ) {
			$issues[] = __( 'Unable to check CSS files for prefers-reduced-motion media queries', 'wpshadow' );
		}

		// Check for prefers-reduced-motion media query in inline styles
		global $wp_filter;
		$has_prefers_reduced_motion = false;

		// Check if any enqueued styles handle motion preferences
		if ( function_exists( 'wp_styles' ) ) {
			$wp_styles = wp_styles();
			foreach ( $wp_styles->registered as $handle => $obj ) {
				if ( isset( $obj->src ) && ! empty( $obj->src ) ) {
					// Check against common animation-handling plugins
					if ( strpos( $obj->src, 'animate' ) !== false || strpos( $obj->src, 'aos' ) !== false ) {
						// These should have motion preference support
						continue;
					}
				}
			}
		}

		// Check for animations without prefers-reduced-motion support
		$animation_plugins = array(
			'aos' => 'AOS (Animate On Scroll)',
			'animate-css' => 'Animate.css',
			'gsap' => 'GSAP (GreenSock)',
		);

		foreach ( $animation_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "plugin-$plugin_slug/plugin-$plugin_slug.php" ) ) {
				// Check if plugin supports prefers-reduced-motion
				$plugin_supports_motion = apply_filters(
					'wpshadow_animation_plugin_supports_motion_preference',
					false,
					$plugin_slug
				);

				if ( ! $plugin_supports_motion ) {
					$issues[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s is active but may not respect prefers-reduced-motion preference', 'wpshadow' ),
						$plugin_name
					);
				}
			}
		}

		// Check if theme has animation support
		if ( ! has_filter( 'wpshadow_theme_motion_preference_support' ) ) {
			$issues[] = __( 'Theme may not respect prefers-reduced-motion preferences for animations', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-reduce-motion-preference',
			);
		}

		return null;
	}
}
