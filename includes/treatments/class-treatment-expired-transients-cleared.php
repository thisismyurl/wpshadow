<?php
/**
 * Treatment: Clear Expired Transients
 *
 * Targeted cleanup of expired transient timeout entries in wp_options.
 * This is a lighter, more focused version of the full transients cleanup
 * treatment — it only deletes expired records, not all transient rows.
 *
 * Skips execution when an external object cache is active (the option
 * table is not used for transient storage in that configuration).
 *
 * Risk level: moderate — deletes database rows. Plugins regenerate
 * transients on next request. Undo requires a database restore.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

/**
 * Clears expired transient entries from the options table.
 */
class Treatment_Expired_Transients_Cleared extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'expired-transients-cleared';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Check treatment preconditions.
	 *
	 * Skip when an external object cache is active.
	 *
	 * @return bool
	 */
	public static function can_apply() {
		if ( wp_using_ext_object_cache() ) {
			return false;
		}

		return parent::can_apply();
	}

	/**
	 * Delete expired transient and timeout rows from wp_options.
	 *
	 * @return array
	 */
	public static function apply() {
		if ( wp_using_ext_object_cache() ) {
			return array(
				'success' => false,
				'message' => __( 'An external object cache is active — transients are not stored in the database and no cleanup is needed.', 'wpshadow' ),
			);
		}

		global $wpdb;

		/*
		 * This cleanup remains on $wpdb because WordPress core does not expose a native helper for
		 * deleting expired timeout rows and their orphaned transient value rows in one pass.
		 * delete_transient() only works when we already know a specific transient key, while this
		 * treatment is intentionally repairing table-level inconsistency across many rows.
		 *
		 * The LEFT JOIN delete is especially important here: it removes orphaned value rows without
		 * hydrating them into PHP, which is both more accurate and less memory-intensive than trying
		 * to emulate the same cleanup with repeated option API calls.
		 */

		// Delete expired timeout rows.
		$timeout_deleted = (int) $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				 WHERE option_name LIKE %s
				   AND option_value < %d",
				'_transient_timeout_%',
				time()
			)
		);

		// Delete matching transient value rows with no timeout (orphaned).
		$value_deleted = (int) $wpdb->query(
			"DELETE v FROM {$wpdb->options} v
			 LEFT JOIN (
				 SELECT REPLACE(option_name, '_transient_timeout_', '_transient_') AS paired
				 FROM {$wpdb->options}
				 WHERE option_name LIKE '_transient_timeout_%'
			 ) paired_keys ON v.option_name = paired_keys.paired
			 WHERE v.option_name LIKE '_transient_%'
			   AND v.option_name NOT LIKE '_transient_timeout_%'
			   AND paired_keys.paired IS NULL"
		);

		$total = $timeout_deleted + $value_deleted;

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: %d: number of rows deleted. */
				_n(
					'%d expired transient entry removed from the database.',
					'%d expired transient entries removed from the database.',
					$total,
					'wpshadow'
				),
				$total
			),
			'details' => array(
				'timeout_rows_deleted' => $timeout_deleted,
				'value_rows_deleted'   => $value_deleted,
				'total_deleted'        => $total,
			),
		);
	}

	/**
	 * Undo is not available — plugins regenerate transients as needed.
	 *
	 * @return array
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => __( 'Deleted transients cannot be restored. Any needed transients will be regenerated automatically.', 'wpshadow' ),
		);
	}
}
