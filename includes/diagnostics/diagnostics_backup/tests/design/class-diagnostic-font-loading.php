<?php
/**
 * Font Loading Performance Diagnostic
 *
 * Checks if fonts use font-display: swap and prevent layout shifts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1145
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font Loading Performance Class
 *
 * Validates font-display strategy to prevent layout shifts.
 * Improves Core Web Vitals (CLS) and user experience.
 *
 * @since 1.5029.1145
 */
class Diagnostic_Font_Loading extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'font-loading-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Font Loading Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates fonts use optimal loading strategy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes enqueued stylesheets for @font-face rules using global $wp_styles.
	 * Checks font-display values to prevent layout shifts.
	 *
	 * @since  1.5029.1145
	 * @return array|null Finding array if font issues found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_font_loading_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get enqueued stylesheets using WordPress API (NO $wpdb).
		global $wp_styles;

		if ( ! $wp_styles instanceof \WP_Styles ) {
			wp_styles();
		}

		$issues          = array();
		$fonts_without_display = array();
		$font_files      = array();

		// Check enqueued styles for font references.
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( empty( $style->src ) ) {
				continue;
			}

			// Fetch stylesheet content if local.
			$src = $style->src;
			if ( 0 === strpos( $src, '/' ) || false !== strpos( $src, home_url() ) ) {
				$response = wp_remote_get( $src, array( 'timeout' => 10 ) );

				if ( ! is_wp_error( $response ) ) {
					$css = wp_remote_retrieve_body( $response );
					$fonts = self::parse_font_faces( $css );

					foreach ( $fonts as $font ) {
						if ( ! isset( $font['display'] ) || 'swap' !== $font['display'] ) {
							$fonts_without_display[] = array(
								'family'  => $font['family'] ?? 'Unknown',
								'handle'  => $handle,
								'display' => $font['display'] ?? 'not set',
							);
						}
						$font_files[] = $font;
					}
				}
			}
		}

		if ( count( $fonts_without_display ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of fonts */
				__( '%d fonts without font-display: swap (causes layout shift)', 'wpshadow' ),
				count( $fonts_without_display )
			);
		}

		// Check for preconnect to font domains.
		$has_preconnect = self::check_font_preconnect();
		if ( ! $has_preconnect && count( $font_files ) > 0 ) {
			$issues[] = __( 'No preconnect hints for external font domains', 'wpshadow' );
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 15;
			if ( count( $fonts_without_display ) > 3 ) {
				$threat_level = 25;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Font loading has %d optimization issues. May cause layout shifts and slow rendering.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/design-font-loading',
				'data'         => array(
					'issues'                 => $issues,
					'fonts_without_display'  => $fonts_without_display,
					'total_fonts'            => count( $font_files ),
					'has_preconnect'         => $has_preconnect,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Parse @font-face rules from CSS.
	 *
	 * @since  1.5029.1145
	 * @param  string $css CSS content.
	 * @return array Array of font-face declarations.
	 */
	private static function parse_font_faces( $css ) {
		$fonts = array();

		preg_match_all( '/@font-face\s*\{([^}]+)\}/is', $css, $matches );

		if ( empty( $matches[1] ) ) {
			return $fonts;
		}

		foreach ( $matches[1] as $font_face ) {
			$font = array();

			// Extract font-family.
			if ( preg_match( '/font-family\s*:\s*[\'"]?([^\'";\n]+)[\'"]?/i', $font_face, $family_match ) ) {
				$font['family'] = trim( $family_match[1] );
			}

			// Extract font-display.
			if ( preg_match( '/font-display\s*:\s*([^;\n]+)/i', $font_face, $display_match ) ) {
				$font['display'] = trim( $display_match[1] );
			}

			$fonts[] = $font;
		}

		return $fonts;
	}

	/**
	 * Check if font preconnect hints exist.
	 *
	 * @since  1.5029.1145
	 * @return bool True if preconnect hints found.
	 */
	private static function check_font_preconnect() {
		$response = wp_remote_get( home_url(), array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for preconnect to common font CDNs.
		$font_domains = array( 'fonts.googleapis.com', 'fonts.gstatic.com', 'use.typekit.net' );

		foreach ( $font_domains as $domain ) {
			if ( preg_match( '/<link[^>]+rel=["\']preconnect["\'][^>]+' . preg_quote( $domain, '/' ) . '/i', $html ) ) {
				return true;
			}
		}

		return false;
	}
}
