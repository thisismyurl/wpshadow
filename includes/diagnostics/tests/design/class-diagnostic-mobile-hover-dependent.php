<?php
/**
 * Diagnostic: Hover-Dependent Functionality Detection
 *
 * Detects CSS :hover states and JavaScript hover events without touch equivalents,
 * making features inaccessible on mobile devices.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4028
 *
 * @package    WPShadow\Diagnostics\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hover-Dependent Functionality Diagnostic
 *
 * Detects hover-only interactions that don't work on touch devices.
 * All hover interactions should have touch equivalents (tap, long-press).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Hover_Dependent extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-hover-dependent';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hover-Dependent Functionality Detection';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hover-only interactions inaccessible on touch devices';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Check for hover-dependent functionality.
	 *
	 * Analyzes theme CSS and JavaScript for hover-only interactions.
	 * Common issues: dropdown menus, tooltips, hidden content.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get active theme.
		$theme      = wp_get_theme();
		$theme_root = get_theme_root();
		$theme_path = $theme_root . '/' . $theme->get_stylesheet();
		$issues     = array();

		// Check CSS files for hover-only interactions.
		$css_files = array();
		
		if ( file_exists( $theme_path . '/style.css' ) ) {
			$css_files[] = $theme_path . '/style.css';
		}

		$hover_count = 0;
		$click_count = 0;
		
		foreach ( $css_files as $css_file ) {
			$content = file_get_contents( $css_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Count :hover pseudo-classes.
			preg_match_all( '/:hover\s*\{/i', $content, $hover_matches );
			$hover_count += count( $hover_matches[0] );
			
			// Check for :active, :focus, or :focus-visible (touch equivalents).
			preg_match_all( '/:(active|focus|focus-visible)\s*\{/i', $content, $focus_matches );
			$click_count += count( $focus_matches[0] );
			
			// Look for dropdown menu hover patterns.
			if ( preg_match( '/\.menu.*:hover.*\{[^}]*display:\s*(block|flex)/i', $content ) ) {
				$issues[] = __( 'Dropdown menu uses hover-only display', 'wpshadow' );
			}
		}

		// Check JavaScript files for hover event listeners.
		$js_files = array();
		if ( is_dir( $theme_path . '/js' ) ) {
			$js_files = glob( $theme_path . '/js/*.js' );
		}
		if ( is_dir( $theme_path . '/assets/js' ) ) {
			$js_files = array_merge( $js_files, glob( $theme_path . '/assets/js/*.js' ) );
		}

		$has_hover_js = false;
		$has_touch_js = false;
		
		foreach ( $js_files as $js_file ) {
			$content = file_get_contents( $js_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Check for hover event listeners.
			if ( preg_match( '/(mouseenter|mouseover|hover)\s*\(/i', $content ) ) {
				$has_hover_js = true;
			}
			
			// Check for touch equivalents.
			if ( preg_match( '/(touchstart|touchend|click|tap)\s*\(/i', $content ) ) {
				$has_touch_js = true;
			}
		}

		// Determine threat level.
		$threat_level = 0;
		
		// High hover usage without touch alternatives.
		if ( $hover_count > 10 && $click_count < ( $hover_count / 2 ) ) {
			$threat_level = 85;
			$issues[]     = sprintf(
				/* translators: 1: hover count, 2: focus/active count */
				__( 'High hover usage (%1$d) with few touch alternatives (%2$d)', 'wpshadow' ),
				$hover_count,
				$click_count
			);
		} elseif ( $hover_count > 5 && $click_count < ( $hover_count / 3 ) ) {
			$threat_level = 75;
		}

		// JavaScript hover without touch.
		if ( $has_hover_js && ! $has_touch_js ) {
			$threat_level = max( $threat_level, 80 );
			$issues[]     = __( 'JavaScript hover events without touch equivalents', 'wpshadow' );
		}

		if ( $threat_level === 0 || empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__(
					'Theme relies on hover interactions that may not work on touch devices: %s. All hover functionality should have touch equivalents (tap, click, long-press) for mobile accessibility.',
					'wpshadow'
				),
				implode( '; ', array_slice( $issues, 0, 3 ) )
			),
			'severity'     => 'critical',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/hover-dependent-functionality',
		);
	}
}
