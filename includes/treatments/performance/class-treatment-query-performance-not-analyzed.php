<?php
/**
 * Query Performance Not Analyzed Treatment
 *
 * Checks query performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Query_Performance_Not_Analyzed Class
 *
 * Performs treatment check for Query Performance Not Analyzed.
 *
 * @since 1.6033.2033
 */
class Treatment_Query_Performance_Not_Analyzed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-performance-not-analyzed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Query Performance Not Analyzed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks query performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'log_slow_queries' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Query performance not analyzed. Enable slow query log and use EXPLAIN to identify missing indexes and optimize queries.',
						'severity'   =>   'high',
						'threat_level'   =>   60,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/query-performance-not-analyzed'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
