<?php
declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Activity Logger - Comprehensive activity tracking system
 *
 * Philosophy: Show value (#9) - Track everything to prove impact
 * Privacy: Beyond Pure (#10) - User actions tracked, no external calls
 *
 * @package WPShadow
 */
class Activity_Logger {

	/**
	 * Option name for activity log
	 */
	const OPTION_NAME = 'wpshadow_activity_log';

	/**
	 * Maximum activities to store (keep last 500)
	 */
	const MAX_ACTIVITIES = 500;

	/**
	 * Log an activity
	 *
	 * @param string $action Action type (e.g., 'diagnostic_run', 'treatment_applied')
	 * @param string $details Human-readable description
	 * @param string $category Optional category (security, performance, etc.)
	 * @param array  $metadata Optional additional data
	 * @return bool Success status
	 */
	public static function log( string $action, string $details, string $category = '', array $metadata = array() ): bool {
		$activity = array(
			'id'        => uniqid( 'activity_', true ),
			'action'    => $action,
			'details'   => $details,
			'category'  => $category,
			'metadata'  => $metadata,
			'user_id'   => get_current_user_id(),
			'user_name' => wp_get_current_user()->display_name,
			'timestamp' => current_time( 'timestamp' ),
			'date'      => current_time( 'mysql' ),
		);

		/**
		 * Filter a log entry before it is persisted.
		 *
		 * @param array  $activity Activity payload.
		 * @param string $action   Action key.
		 * @param string $details  Description.
		 * @param string $category Category slug.
		 * @param array  $metadata Metadata array.
		 */
		$activity = apply_filters( 'wpshadow_activity_entry', $activity, $action, $details, $category, $metadata );

		// Get existing log
		$log = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $log ) ) {
			$log = array();
		}

		// Add new activity at the beginning
		array_unshift( $log, $activity );

		// Trim to max size
		if ( count( $log ) > self::MAX_ACTIVITIES ) {
			$log = array_slice( $log, 0, self::MAX_ACTIVITIES );
		}

		$updated = update_option( self::OPTION_NAME, $log );

		/**
		 * Fires after an activity entry is stored.
		 *
		 * @param array $activity Activity payload.
		 */
		do_action( 'wpshadow_activity_logged', $activity );

		return $updated;
	}

	/**
	 * Get activity log with optional filters
	 *
	 * @param array $filters Optional filters (category, action, user_id, date_from, date_to)
	 * @param int   $limit Optional limit (default: 50)
	 * @param int   $offset Optional offset (default: 0)
	 * @return array Filtered activities
	 */
	public static function get_activities( array $filters = array(), int $limit = 50, int $offset = 0 ): array {
		$log = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $log ) ) {
			return array();
		}

		// Apply filters
		if ( ! empty( $filters ) ) {
			$log = array_filter(
				$log,
				function ( $activity ) use ( $filters ) {
					// Category filter (supports both single and array)
					if ( ! empty( $filters['category'] ) ) {
						$category = $filters['category'];
						if ( is_array( $category ) ) {
							if ( ! in_array( $activity['category'], $category, true ) ) {
								return false;
							}
						} elseif ( $activity['category'] !== $category ) {
							return false;
						}
					}

					// Categories filter (alias for category as array)
					if ( ! empty( $filters['categories'] ) && is_array( $filters['categories'] ) ) {
						if ( ! in_array( $activity['category'], $filters['categories'], true ) ) {
							return false;
						}
					}

					// Action filter (supports both single and array)
					if ( ! empty( $filters['action'] ) ) {
						$action = $filters['action'];
						if ( is_array( $action ) ) {
							if ( ! in_array( $activity['action'], $action, true ) ) {
								return false;
							}
						} elseif ( $activity['action'] !== $action ) {
							return false;
						}
					}

					// Actions filter (alias for action as array)
					if ( ! empty( $filters['actions'] ) && is_array( $filters['actions'] ) ) {
						if ( ! in_array( $activity['action'], $filters['actions'], true ) ) {
							return false;
						}
					}

					// User filter
					if ( isset( $filters['user_id'] ) && $activity['user_id'] !== $filters['user_id'] ) {
						return false;
					}

					// Date range filter
					if ( ! empty( $filters['date_from'] ) && $activity['timestamp'] < strtotime( $filters['date_from'] ) ) {
						return false;
					}
					if ( ! empty( $filters['date_to'] ) && $activity['timestamp'] > strtotime( $filters['date_to'] ) ) {
						return false;
					}

					// Search filter
					if ( ! empty( $filters['search'] ) ) {
						$search     = strtolower( $filters['search'] );
						$searchable = strtolower( $activity['details'] . ' ' . $activity['action'] );
						if ( strpos( $searchable, $search ) === false ) {
							return false;
						}
					}

					return true;
				}
			);
		}

		// Apply pagination
		$total = count( $log );
		$log   = array_slice( $log, $offset, $limit );

		return array(
			'activities' => $log,
			'total'      => $total,
			'limit'      => $limit,
			'offset'     => $offset,
		);
	}

	/**
	 * Get activity counts by action type
	 *
	 * @return array Action type counts
	 */
	public static function get_action_counts(): array {
		$log = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $log ) ) {
			return array();
		}

		$counts = array();
		foreach ( $log as $activity ) {
			$action = $activity['action'];
			if ( ! isset( $counts[ $action ] ) ) {
				$counts[ $action ] = 0;
			}
			++$counts[ $action ];
		}

		arsort( $counts );
		return $counts;
	}

	/**
	 * Get activity counts by category
	 *
	 * @return array Category counts
	 */
	public static function get_category_counts(): array {
		$log = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $log ) ) {
			return array();
		}

		$counts = array();
		foreach ( $log as $activity ) {
			$category = $activity['category'] ? $activity['category'] : 'uncategorized';
			if ( ! isset( $counts[ $category ] ) ) {
				$counts[ $category ] = 0;
			}
			++$counts[ $category ];
		}

		arsort( $counts );
		return $counts;
	}

	/**
	 * Export activities to CSV
	 *
	 * @param array $filters Optional filters
	 * @return string CSV content
	 */
	public static function export_csv( array $filters = array() ): string {
		$result     = self::get_activities( $filters, 10000, 0 );
		$activities = $result['activities'];

		// CSV header
		$csv = "Timestamp,User,Action,Category,Details\n";

		// CSV rows
		foreach ( $activities as $activity ) {
			$csv .= sprintf(
				'"%s","%s","%s","%s","%s"' . "\n",
				self::escape_csv_value( $activity['date'] ),
				self::escape_csv_value( $activity['user_name'] ),
				self::escape_csv_value( $activity['action'] ),
				self::escape_csv_value( $activity['category'] ),
				self::escape_csv_value( $activity['details'] )
			);
		}

		return $csv;
	}

	/**
	 * Escape CSV value to prevent formula injection
	 *
	 * Prevents CSV injection by prepending single quote to values that
	 * start with potentially dangerous characters (=, +, -, @, tab, return).
	 *
	 * @since  1.2602.0200
	 * @param  string $value Value to escape.
	 * @return string Escaped value.
	 */
	private static function escape_csv_value( string $value ): string {
		// Prevent formula injection by prepending single quote
		if ( in_array( substr( $value, 0, 1 ), array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}

		// Escape double quotes for CSV format
		$value = str_replace( '"', '""', $value );

		return $value;
	}

	/**
	 * Delete activity entries older than a cutoff date.
	 *
	 * @param string $cutoff_date Date string parsable by strtotime().
	 * @return int Number of removed entries.
	 */
	public static function delete_old_entries( string $cutoff_date ): int {
		$log = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $log ) || empty( $log ) ) {
			return 0;
		}

		$cutoff_timestamp = strtotime( $cutoff_date );
		if ( false === $cutoff_timestamp ) {
			return 0;
		}

		$original_count = count( $log );
		$log            = array_values(
			array_filter(
				$log,
				function ( $activity ) use ( $cutoff_timestamp ) {
					return isset( $activity['timestamp'] ) && $activity['timestamp'] >= $cutoff_timestamp;
				}
			)
		);

		update_option( self::OPTION_NAME, $log );

		return $original_count - count( $log );
	}

	/**
	 * Clear old activities (older than specified days)
	 *
	 * @param int $days Days to keep (default: 90)
	 * @return int Number of activities removed
	 */
	public static function prune( int $days = 90 ): int {
		$log = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $log ) ) {
			return 0;
		}

		$cutoff         = current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS );
		$original_count = count( $log );

		$log = array_filter(
			$log,
			function ( $activity ) use ( $cutoff ) {
				return $activity['timestamp'] >= $cutoff;
			}
		);

		update_option( self::OPTION_NAME, $log );

		return $original_count - count( $log );
	}

	/**
	 * Get recent activities (last 10)
	 *
	 * @return array Recent activities
	 */
	public static function get_recent( int $count = 10 ): array {
		$result = self::get_activities( array(), $count, 0 );
		return $result['activities'];
	}
}
