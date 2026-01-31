<?php
/**
 * Fallback Font Loading Not Configured Diagnostic
 *
 * Checks if fallback fonts are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fallback Font Loading Not Configured Diagnostic Class
 *
 * Detects missing fallback font configuration.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Fallback_Font_Loading_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'fallback-font-loading-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Fallback Font Loading Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if fallback fonts are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for fallback font stack
		if ( ! has_filter( 'wp_head', 'add_fallback_fonts' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Fallback font loading is not configured. Define font-family stacks with system fonts as fallbacks to prevent layout shift and ensure text remains readable while web fonts load.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/fallback-font-loading-not-configured',
			);
		}

		return null;
	}
}
