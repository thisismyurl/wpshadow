<?php
/**
 * Diagnostic: Auto-Increment Status Check
 *
 * Checks database table auto-increment values to detect potential issues.
 * Large gaps or reaching max values can indicate problems.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Auto_Increment_Status
 *
 * Monitors auto-increment values in WordPress database tables.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Auto_Increment_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'auto-increment-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Auto-Increment Status Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks database auto-increment values for potential issues';

	/**
	 * Check database auto-increment status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get all WordPress tables with auto-increment.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME, AUTO_INCREMENT
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME LIKE %s
				AND AUTO_INCREMENT IS NOT NULL",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_A
		);

		if ( empty( $tables ) ) {
			return null; // No tables with auto-increment found.
		}

		$issues = array();

		foreach ( $tables as $table ) {
			$table_name      = $table['TABLE_NAME'];
			$auto_increment  = (int) $table['AUTO_INCREMENT'];

			// INT UNSIGNED max value is 4,294,967,295.
			// BIGINT UNSIGNED max value is 18,446,744,073,709,551,615.
			// Assume INT UNSIGNED for now (most common).
			$max_value = 4294967295;
			$threshold = $max_value * 0.9; // 90% threshold.

			// Warn if auto-increment is approaching max value.
			if ( $auto_increment > $threshold ) {
				$percentage = ( $auto_increment / $max_value ) * 100;
				$issues[ $table_name ] = sprintf(
					/* translators: 1: Percentage of max value, 2: Current value, 3: Max value */
					__( 'Auto-increment at %.2f%% of max value (%d/%d)', 'wpshadow' ),
					$percentage,
					$auto_increment,
					$max_value
				);
			}

			// Warn if auto-increment has large gaps (potential deleted rows).
			// Get actual row count.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );

			// Calculate gap percentage.
			if ( $row_count > 0 ) {
				$gap_percentage = ( ( $auto_increment - $row_count ) / $auto_increment ) * 100;

				// Warn if gap is more than 50%.
				if ( $gap_percentage > 50 ) {
					$issues[ $table_name ] = sprintf(
						/* translators: 1: Gap percentage, 2: Current auto-increment, 3: Actual row count */
						__( 'Large gap detected: %.2f%% (auto-increment: %d, rows: %d)', 'wpshadow' ),
						$gap_percentage,
						$auto_increment,
						$row_count
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of tables with issues */
					_n(
						'%d database table has auto-increment issues',
						'%d database tables have auto-increment issues',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/auto_increment_status',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		// Auto-increment values are healthy.
		return null;
	}
}
