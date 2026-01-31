<?php
/**
 * Caching Strategy Not Implemented Diagnostic
 *
 * Checks if caching strategy is implemented.
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
 * Caching Strategy Not Implemented Diagnostic Class
 *
 * Detects missing caching strategy.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Caching_Strategy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'caching-strategy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Caching Strategy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if caching strategy is implemented';

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
		// Check for caching plugin
		if ( ! is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) && ! is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Caching strategy is not implemented. Enable page caching, browser caching, and server-level caching for optimal performance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/caching-strategy-not-implemented',
			);
		}

		return null;
	}
}
