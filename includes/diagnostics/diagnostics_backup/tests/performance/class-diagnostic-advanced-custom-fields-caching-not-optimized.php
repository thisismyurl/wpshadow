<?php
/**
 * Advanced Custom Fields Caching Not Optimized Diagnostic
 *
 * Checks if ACF caching is optimized.
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
 * Advanced Custom Fields Caching Not Optimized Diagnostic Class
 *
 * Detects unoptimized ACF caching.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Advanced_Custom_Fields_Caching_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'advanced-custom-fields-caching-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Advanced Custom Fields Caching Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if ACF caching is optimized';

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
		// Check if ACF is active and caching enabled
		if ( is_plugin_active( 'advanced-custom-fields/acf.php' ) && ! defined( 'ACF_EARLY_ACCESS' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Advanced Custom Fields caching is not optimized. Enable ACF caching and integrate with persistent object cache for better performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/advanced-custom-fields-caching-not-optimized',
			);
		}

		return null;
	}
}
