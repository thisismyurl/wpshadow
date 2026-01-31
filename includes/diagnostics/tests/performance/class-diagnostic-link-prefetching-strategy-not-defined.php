<?php
/**
 * Link Prefetching Strategy Not Defined Diagnostic
 *
 * Checks if link prefetching strategy is defined.
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
 * Link Prefetching Strategy Not Defined Diagnostic Class
 *
 * Detects missing link prefetching.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Link_Prefetching_Strategy_Not_Defined extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'link-prefetching-strategy-not-defined';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Link Prefetching Strategy Not Defined';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if link prefetching strategy is defined';

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
		// Check for link prefetch implementation
		if ( ! has_filter( 'wp_head', 'add_prefetch_links' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Link prefetching strategy is not defined. Prefetch high-probability next links to reduce perceived navigation latency and improve user experience.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/link-prefetching-strategy-not-defined',
			);
		}

		return null;
	}
}
