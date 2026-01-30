<?php
/**
 * Theme Font Loading Diagnostic
 *
 * Detects missing or incorrectly loaded web fonts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1715
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Class
 *
 * Validates web font implementation.
 *
 * @since 1.5029.1715
 */
class Diagnostic_Theme_Font_Loading extends Diagnostic_Base {

	protected static $slug        = 'theme-font-loading';
	protected static $title       = 'Theme Font Loading';
	protected static $description = 'Detects font loading issues';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_font_loading';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wp_styles;

		if ( empty( $wp_styles->registered ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$font_issues = array();

		foreach ( $wp_styles->registered as $handle => $style ) {
			// Check for Google Fonts.
			if ( strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
				// Check if using display=swap.
				if ( strpos( $style->src, 'display=swap' ) === false ) {
					$font_issues[] = array(
						'handle' => $handle,
						'url' => $style->src,
						'issue' => 'Missing display=swap parameter (causes render blocking)',
						'severity' => 'medium',
					);
				}

				// Check if loaded early.
				if ( $style->extra && isset( $style->extra['after'] ) ) {
					$font_issues[] = array(
						'handle' => $handle,
						'issue' => 'Font loaded after other styles (should preconnect)',
						'severity' => 'low',
					);
				}
			}
		}

		// Check for local font files.
		$theme_dir = get_stylesheet_directory();
		$font_formats = array( 'woff2', 'woff', 'ttf', 'otf' );
		$local_fonts  = array();

		foreach ( $font_formats as $format ) {
			$fonts = glob( $theme_dir . '/fonts/*.' . $format );
			if ( ! empty( $fonts ) ) {
				$local_fonts = array_merge( $local_fonts, $fonts );
			}
		}

		// Check if local fonts are properly enqueued.
		if ( ! empty( $local_fonts ) && empty( $wp_styles->registered ) ) {
			$font_issues[] = array(
				'issue' => sprintf( '%d local font files found but not enqueued', count( $local_fonts ) ),
				'severity' => 'medium',
			);
		}

		if ( ! empty( $font_issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d font loading issues detected. Fix to improve performance.', 'wpshadow' ),
					count( $font_issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-font-loading',
				'data'         => array(
					'font_issues' => $font_issues,
					'total_issues' => count( $font_issues ),
					'local_fonts_count' => count( $local_fonts ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
