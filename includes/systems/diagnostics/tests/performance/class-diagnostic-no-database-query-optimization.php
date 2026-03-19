<?php
/**
 * No Database Query Optimization Diagnostic
 *
 * Detects when database queries are not optimized,
 * causing slow page loads from inefficient queries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Database Query Optimization
 *
 * Checks whether database queries are optimized
 * to prevent slow page loads.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Database_Query_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-database-query-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Optimization';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for unoptimized database tables
		$tables = $wpdb->get_results( "SHOW TABLE STATUS FROM " . DB_NAME ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( empty( $tables ) ) {
			return null;
		}

		$data_free = 0;
		foreach ( $tables as $table ) {
			$data_free += (int) $table->Data_free;
		}

		// If significant fragmentation exists, suggest optimization
		if ( $data_free > 1048576 ) { // > 1MB of fragmentation
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'Your database has %.1f MB of fragmentation, which slows queries. Database fragmentation happens as you add/delete data—deleted records leave gaps. Over time, queries need to scan more blocks to find data (think looking through a book with torn pages). Solution: OPTIMIZE tables (WordPress plugins can do this). After optimization, typical improvement: 5-20%% faster page load, especially for query-heavy pages like archives.',
						'wpshadow'
					),
					$data_free / 1048576
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'data_free'     => $data_free,
				'business_impact' => array(
					'metric'         => 'Database Query Speed',
					'potential_gain' => '+5-20% faster page loads',
					'roi_explanation' => 'Database optimization removes fragmentation, reducing query time especially for complex queries.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/database-query-optimization',
			);
		}

		return null;
	}
}
