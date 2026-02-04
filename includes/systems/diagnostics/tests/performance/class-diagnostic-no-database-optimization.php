<?php
/**
 * No Database Optimization Diagnostic
 *
 * Detects when database is not optimized,
 * causing slow queries and poor performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Database Optimization
 *
 * Checks whether database is optimized and
 * maintenance routines are in place.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Database_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-database-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Database Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether database is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for database optimization plugins
		$has_db_optimization = is_plugin_active( 'wp-dbmanager/wp-dbmanager.php' ) ||
			is_plugin_active( 'advanced-database-cleaner/advanced-database-cleaner.php' ) ||
			is_plugin_active( 'wp-optimize/wp-optimize.php' );

		if ( ! $has_db_optimization ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your database isn\'t being optimized, which means it\'s accumulating bloat. WordPress accumulates: old revisions, spam comments, transient cache, unused tables. Over time, queries get slower. Database optimization removes bloat, rebuilds indexes, and improves query performance. It\'s like cleaning out a garage—things run faster and smoother. Running optimization monthly can improve database performance by 30-50%.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Database Performance',
					'potential_gain' => '30-50% faster database queries',
					'roi_explanation' => 'Database optimization removes bloat and improves query performance, making the entire site faster with minimal effort.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/database-optimization',
			);
		}

		return null;
	}
}
