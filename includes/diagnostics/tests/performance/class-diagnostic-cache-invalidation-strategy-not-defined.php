<?php
/**
 * Cache Invalidation Strategy Not Defined Diagnostic
 *
 * Checks if cache invalidation strategy is defined.
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
 * Cache Invalidation Strategy Not Defined Diagnostic Class
 *
 * Detects missing cache invalidation strategy.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Cache_Invalidation_Strategy_Not_Defined extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-invalidation-strategy-not-defined';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Invalidation Strategy Not Defined';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cache invalidation strategy is defined';

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
		// Check for cache invalidation on post save
		if ( ! has_action( 'save_post', 'invalidate_post_cache' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Cache invalidation strategy is not defined. Implement automatic cache clearing on post updates, comment changes, and settings modifications to keep content fresh.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/cache-invalidation-strategy-not-defined',
			);
		}

		return null;
	}
}
