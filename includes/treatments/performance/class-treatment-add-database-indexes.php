<?php
/**
 * Treatment: Add Database Indexes
 *
 * Adds recommended database indexes for common queries.
 *
 * Philosophy: Ridiculously Good (#7) - Free query optimization
 * KB Link: https://wpshadow.com/kb/missing-database-indexes
 * Training: https://wpshadow.com/training/missing-database-indexes
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Database Indexes treatment
 */
class Treatment_Add_Database_Indexes extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = array() ): bool {
		global $wpdb;

		$added_indexes = array();

		// Define recommended indexes
		$recommended_indexes = array(
			array(
				'table'     => $wpdb->postmeta,
				'name'      => 'meta_key_value',
				'columns'   => 'meta_key, meta_value(191)',
				'check_col' => 'meta_key',
			),
			array(
				'table'     => $wpdb->usermeta,
				'name'      => 'meta_key_value',
				'columns'   => 'meta_key, meta_value(191)',
				'check_col' => 'meta_key',
			),
			array(
				'table'     => $wpdb->posts,
				'name'      => 'post_date_gmt',
				'columns'   => 'post_date_gmt',
				'check_col' => 'post_date_gmt',
			),
			array(
				'table'     => $wpdb->comments,
				'name'      => 'comment_approved_date_gmt',
				'columns'   => 'comment_approved, comment_date_gmt',
				'check_col' => 'comment_approved',
			),
		);

		foreach ( $recommended_indexes as $index ) {
			// Check if index already exists
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) 
					FROM information_schema.STATISTICS 
					WHERE table_schema = %s 
					AND table_name = %s 
					AND index_name = %s',
					DB_NAME,
					$index['table'],
					$index['name']
				)
			);

			if ( $exists ) {
				continue; // Already exists
			}

			// Add the index
			$sql = sprintf(
				'ALTER TABLE `%s` ADD INDEX `%s` (%s)',
				$index['table'],
				$index['name'],
				$index['columns']
			);

			$result = $wpdb->query( $sql );

			if ( $result !== false ) {
				$added_indexes[] = array(
					'table'   => $index['table'],
					'name'    => $index['name'],
					'columns' => $index['columns'],
				);
			}
		}

		// Create backup with added indexes
		self::create_backup(
			array(
				'added_indexes' => $added_indexes,
				'timestamp'     => time(),
			)
		);

		// Track KPI
		if ( ! empty( $added_indexes ) ) {
			KPI_Tracker::record_treatment_applied( __CLASS__, 5 );
		}

		return ! empty( $added_indexes );
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		global $wpdb;

		$backup = self::restore_backup();
		if ( ! $backup || empty( $backup['added_indexes'] ) ) {
			return false;
		}

		// Remove added indexes
		foreach ( $backup['added_indexes'] as $index ) {
			$wpdb->query(
				sprintf(
					'ALTER TABLE `%s` DROP INDEX `%s`',
					$index['table'],
					$index['name']
				)
			);
		}

		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Add Database Indexes', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Adds recommended database indexes for common queries. Indexes speed up searches without changing code. Safe to run on live sites. <a href="%s" target="_blank">Learn about database indexes</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/missing-database-indexes'
		);
	}
}
