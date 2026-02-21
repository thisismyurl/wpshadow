<?php
/**
 * Database Query Performance and Indexing
 *
 * Validates database query performance and index optimization.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Query_Performance Class
 *
 * Checks database query performance and indexing issues.
 *
 * @since 1.6030.2148
 */
class Treatment_Database_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database query performance and index usage';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Query_Performance' );
	}

	/**
	 * Check for missing indexes.
	 *
	 * @since  1.6030.2148
	 * @return array Missing indexes.
	 */
	private static function check_missing_indexes() {
		global $wpdb;

		$missing = array();

		// Check for common missing indexes
		$indexes_to_check = array(
			array(
				'table'  => $wpdb->posts,
				'column' => 'post_author',
				'name'   => 'idx_post_author',
			),
			array(
				'table'  => $wpdb->posts,
				'column' => 'post_date',
				'name'   => 'idx_post_date',
			),
			array(
				'table'  => $wpdb->postmeta,
				'column' => 'meta_key',
				'name'   => 'idx_meta_key',
			),
			array(
				'table'  => $wpdb->comments,
				'column' => 'comment_post_ID',
				'name'   => 'idx_comment_post_id',
			),
		);

		foreach ( $indexes_to_check as $index ) {
			$exists = $wpdb->get_row(
				$wpdb->prepare(
					"SHOW INDEX FROM %i WHERE Column_name = %s",
					$index['table'],
					$index['column']
				)
			);

			if ( ! $exists ) {
				$missing[] = array(
					'table'  => $index['table'],
					'column' => $index['column'],
					'suggested_name' => $index['name'],
				);
			}
		}

		return $missing;
	}
}
