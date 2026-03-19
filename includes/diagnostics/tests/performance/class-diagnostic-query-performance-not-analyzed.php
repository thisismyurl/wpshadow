<?php
/**
 * Query Performance Not Analyzed Diagnostic
 *
 * Checks query performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Query_Performance_Not_Analyzed Class
 *
 * Performs diagnostic check for Query Performance Not Analyzed.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Query_Performance_Not_Analyzed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-performance-not-analyzed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query Performance Not Analyzed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks query performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'log_slow_queries' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Query performance not analyzed. Enable slow query log and use EXPLAIN to identify missing indexes and optimize queries.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/query-performance-not-analyzed',
			);
		}

		return null;
	}
}
