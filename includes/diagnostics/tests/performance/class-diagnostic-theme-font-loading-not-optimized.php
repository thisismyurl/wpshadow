<?php
/**
 * Theme Font Loading Not Optimized Diagnostic
 *
 * Checks if theme fonts are loading efficiently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Not Optimized Diagnostic Class
 *
 * Detects suboptimal font loading.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Theme_Font_Loading_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-font-loading-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Font Loading Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme fonts are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for Google Fonts loaded from external domain
		global $wp_styles;

		if ( ! empty( $wp_styles ) && ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $object ) {
				if ( strpos( $object->src, 'fonts.googleapis.com' ) !== false || 
					 strpos( $object->src, 'fonts.gstatic.com' ) !== false ) {
					// External fonts detected, not optimized
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => __( 'Google Fonts are being loaded from external domain. Consider self-hosting fonts or using font-display: swap for better performance.', 'wpshadow' ),
						'severity'      => 'medium',
						'threat_level'  => 40,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/theme-font-loading-not-optimized',
					);
				}
			}
		}

		return null;
	}
}
