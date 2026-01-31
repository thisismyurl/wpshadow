<?php
/**
 * Font Loading Strategy Not Optimized Diagnostic
 *
 * Checks if fonts are optimized for loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font Loading Strategy Not Optimized Diagnostic Class
 *
 * Detects unoptimized font loading.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Font_Loading_Strategy_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'font-loading-strategy-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Font Loading Strategy Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if font loading is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		if ( ! $wp_styles ) {
			return null;
		}

		// Check if Google Fonts are loaded
		$fonts_from_google = 0;
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( $style->src && strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
				$fonts_from_google++;
			}
		}

		if ( $fonts_from_google > 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d Google Font requests are made. Load fonts with font-display or preload to improve performance.', 'wpshadow' ),
					$fonts_from_google
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/font-loading-strategy-not-optimized',
			);
		}

		return null;
	}
}
