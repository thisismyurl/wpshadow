<?php
/**
 * Report Snapshot Manager
 *
 * Handles saving and comparing historical report snapshots.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Snapshot_Manager Class
 *
 * Manages report snapshots for historical comparison.
 *
 * @since 1.6093.1200
 */
class Report_Snapshot_Manager {
	/**
	 * Option key for next snapshot ID.
	 *
	 * @var string
	 */
	private const OPTION_NEXT_ID = 'wpshadow_report_snapshot_next_id';

	/**
	 * Option key for known report IDs.
	 *
	 * @var string
	 */
	private const OPTION_REPORTS = 'wpshadow_report_snapshot_reports';

	/**
	 * Save a report snapshot
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  array  $data      Report data.
	 * @param  array  $metadata  Additional metadata.
	 * @return int|false Snapshot ID or false on failure.
	 */
	public static function save_snapshot( $report_id, $data, $metadata = array() ) {
		self::maybe_create_table();

		$report_id   = sanitize_key( $report_id );
		$snapshot_id = self::get_next_snapshot_id();
		$snapshot    = array(
			'id'             => $snapshot_id,
			'report_id'      => $report_id,
			'data'           => is_array( $data ) ? $data : array(),
			'metadata'       => is_array( $metadata ) ? $metadata : array(),
			'created_at'     => current_time( 'mysql' ),
			'findings_count' => isset( $data['findings'] ) && is_array( $data['findings'] ) ? count( $data['findings'] ) : 0,
		);

		if ( ! self::save_snapshot_record( $snapshot ) ) {
			return false;
		}

		$report_snapshot_ids = self::get_report_snapshot_ids( $report_id );
		array_unshift( $report_snapshot_ids, $snapshot_id );
		self::set_report_snapshot_ids( $report_id, $report_snapshot_ids );

		$known_reports = get_option( self::OPTION_REPORTS, array() );
		if ( ! is_array( $known_reports ) ) {
			$known_reports = array();
		}
		if ( ! in_array( $report_id, $known_reports, true ) ) {
			$known_reports[] = $report_id;
			update_option( self::OPTION_REPORTS, $known_reports, false );
		}

			/**
			 * Fires after a report snapshot is saved.
			 *
			 * @since 1.6093.1200
			 *
			 * @param int    $snapshot_id Snapshot ID.
			 * @param string $report_id Report ID.
			 * @param array  $data Report data.
			 */
			do_action( 'wpshadow_after_snapshot_saved', $snapshot_id, $report_id, $data );

			return $snapshot_id;
	}

	/**
	 * Get snapshots for a report
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  int    $limit     Number of snapshots to retrieve.
	 * @return array Snapshots.
	 */
	public static function get_snapshots( $report_id, $limit = 10 ) {
		return self::get_snapshots_paginated( $report_id, $limit, 0 );
	}

	/**
	 * Check whether the snapshots table exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True when table exists.
	 */
	public static function has_snapshots_table() {
		return true;
	}

	/**
	 * Get a snapshot by ID.
	 *
	 * @since 1.6093.1200
	 * @param  int $snapshot_id Snapshot ID.
	 * @return array|null Snapshot data or null when not found.
	 */
	public static function get_snapshot_by_id( $snapshot_id ) {
		$snapshot = get_option( self::get_snapshot_option_key( (int) $snapshot_id ), null );

		if ( ! is_array( $snapshot ) ) {
			return null;
		}

		$snapshot['id']             = (int) ( $snapshot['id'] ?? 0 );
		$snapshot['report_id']      = sanitize_key( $snapshot['report_id'] ?? '' );
		$snapshot['data']           = is_array( $snapshot['data'] ?? null ) ? $snapshot['data'] : array();
		$snapshot['metadata']       = is_array( $snapshot['metadata'] ?? null ) ? $snapshot['metadata'] : array();
		$snapshot['created_at']     = (string) ( $snapshot['created_at'] ?? '' );
		$snapshot['findings_count'] = (int) ( $snapshot['findings_count'] ?? 0 );

		if ( $snapshot['id'] <= 0 || '' === $snapshot['report_id'] ) {
			return null;
		}

		return $snapshot;
	}

	/**
	 * Get total snapshot count for a report.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @return int Snapshot count.
	 */
	public static function get_snapshots_count( $report_id ) {
		return count( self::get_report_snapshot_ids( sanitize_key( $report_id ) ) );
	}

	/**
	 * Get paginated snapshots for a report.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  int    $limit     Number of snapshots to retrieve.
	 * @param  int    $offset    Result offset.
	 * @return array Snapshots.
	 */
	public static function get_snapshots_paginated( $report_id, $limit = 10, $offset = 0 ) {
		$report_id = sanitize_key( $report_id );
		$ids       = self::get_report_snapshot_ids( $report_id );
		$ids       = array_slice( $ids, max( 0, (int) $offset ), max( 1, (int) $limit ) );

		$snapshots = array();
		foreach ( $ids as $snapshot_id ) {
			$snapshot = self::get_snapshot_by_id( (int) $snapshot_id );
			if ( null !== $snapshot ) {
				$snapshots[] = $snapshot;
			}
		}

		return $snapshots;
	}

	/**
	 * Get paginated snapshots for a report and user.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  int    $user_id   User ID.
	 * @param  int    $limit     Number of snapshots to retrieve.
	 * @param  int    $offset    Result offset.
	 * @return array Snapshots.
	 */
	public static function get_snapshots_for_user( $report_id, $user_id, $limit = 10, $offset = 0 ) {
		$all_snapshots = self::get_snapshots_paginated( $report_id, PHP_INT_MAX, 0 );
		$filtered      = array();

		foreach ( $all_snapshots as $snapshot ) {
			if ( isset( $snapshot['metadata']['user_id'] ) && (int) $snapshot['metadata']['user_id'] === (int) $user_id ) {
				$filtered[] = $snapshot;
			}
		}

		return array_slice( $filtered, max( 0, (int) $offset ), max( 1, (int) $limit ) );
	}

	/**
	 * Get total snapshot count for a report and user.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  int    $user_id   User ID.
	 * @return int Snapshot count.
	 */
	public static function get_snapshots_for_user_count( $report_id, $user_id ) {
		return count( self::get_snapshots_for_user( $report_id, $user_id, PHP_INT_MAX, 0 ) );
	}

	/**
	 * Delete snapshots for a report and user.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  int    $user_id   User ID.
	 * @return int Number of snapshots deleted.
	 */
	public static function delete_snapshots_for_user( $report_id, $user_id ) {
		$report_id = sanitize_key( $report_id );
		$ids       = self::get_report_snapshot_ids( $report_id );
		$deleted   = 0;

		foreach ( $ids as $snapshot_id ) {
			$snapshot = self::get_snapshot_by_id( (int) $snapshot_id );
			if ( null === $snapshot ) {
				continue;
			}

			if ( isset( $snapshot['metadata']['user_id'] ) && (int) $snapshot['metadata']['user_id'] === (int) $user_id ) {
				delete_option( self::get_snapshot_option_key( (int) $snapshot_id ) );
				$deleted++;
			}
		}

		if ( $deleted > 0 ) {
			$remaining_ids = array_values(
				array_filter(
					$ids,
					function ( $snapshot_id ) {
						return null !== self::get_snapshot_by_id( (int) $snapshot_id );
					}
				)
			);

			self::set_report_snapshot_ids( $report_id, $remaining_ids );
		}

		return $deleted;
	}

	/**
	 * Compare two snapshots
	 *
	 * @since 1.6093.1200
	 * @param  int $snapshot_id_1 First snapshot ID.
	 * @param  int $snapshot_id_2 Second snapshot ID.
	 * @return array Comparison data.
	 */
	public static function compare_snapshots( $snapshot_id_1, $snapshot_id_2 ) {
		$snapshot1 = self::get_snapshot_by_id( (int) $snapshot_id_1 );
		$snapshot2 = self::get_snapshot_by_id( (int) $snapshot_id_2 );
		
		if ( ! $snapshot1 || ! $snapshot2 ) {
			return array( 'error' => 'Snapshots not found' );
		}
		
		$data1 = $snapshot1['data'];
		$data2 = $snapshot2['data'];
		
		$comparison = array(
			'snapshot1' => array(
				'id'        => $snapshot_id_1,
				'date'      => $snapshot1['created_at'],
				'findings'  => $snapshot1['findings_count'],
			),
			'snapshot2' => array(
				'id'        => $snapshot_id_2,
				'date'      => $snapshot2['created_at'],
				'findings'  => $snapshot2['findings_count'],
			),
			'delta' => array(
				'findings_change' => $snapshot2['findings_count'] - $snapshot1['findings_count'],
				'new_issues'      => self::find_new_issues( $data1, $data2 ),
				'resolved_issues' => self::find_resolved_issues( $data1, $data2 ),
			),
		);
		
		return $comparison;
	}

	/**
	 * Find new issues between snapshots
	 *
	 * @since 1.6093.1200
	 * @param  array $old_data Old snapshot data.
	 * @param  array $new_data New snapshot data.
	 * @return array New issues.
	 */
	private static function find_new_issues( $old_data, $new_data ) {
		$old_ids = array();
		if ( isset( $old_data['findings'] ) ) {
			$old_ids = wp_list_pluck( $old_data['findings'], 'id' );
		}
		
		$new_issues = array();
		if ( isset( $new_data['findings'] ) ) {
			foreach ( $new_data['findings'] as $finding ) {
				if ( ! in_array( $finding['id'], $old_ids, true ) ) {
					$new_issues[] = $finding;
				}
			}
		}
		
		return $new_issues;
	}

	/**
	 * Find resolved issues between snapshots
	 *
	 * @since 1.6093.1200
	 * @param  array $old_data Old snapshot data.
	 * @param  array $new_data New snapshot data.
	 * @return array Resolved issues.
	 */
	private static function find_resolved_issues( $old_data, $new_data ) {
		$new_ids = array();
		if ( isset( $new_data['findings'] ) ) {
			$new_ids = wp_list_pluck( $new_data['findings'], 'id' );
		}
		
		$resolved_issues = array();
		if ( isset( $old_data['findings'] ) ) {
			foreach ( $old_data['findings'] as $finding ) {
				if ( ! in_array( $finding['id'], $new_ids, true ) ) {
					$resolved_issues[] = $finding;
				}
			}
		}
		
		return $resolved_issues;
	}

	/**
	 * Get trend data for a report
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report identifier.
	 * @param  int    $days      Number of days to analyze.
	 * @return array Trend data.
	 */
	public static function get_trend_data( $report_id, $days = 30 ) {
		$cutoff_time = strtotime( "-{$days} days" );
		$snapshots   = self::get_snapshots_paginated( $report_id, PHP_INT_MAX, 0 );
		$trend_rows  = array();

		foreach ( $snapshots as $snapshot ) {
			$created_time = strtotime( $snapshot['created_at'] );
			if ( false !== $created_time && $created_time >= $cutoff_time ) {
				$trend_rows[] = array(
					'created_at'     => $snapshot['created_at'],
					'findings_count' => (int) $snapshot['findings_count'],
				);
			}
		}

		usort(
			$trend_rows,
			function ( $left, $right ) {
				return strcmp( $left['created_at'], $right['created_at'] );
			}
		);
		
		return array(
			'report_id' => $report_id,
			'period'    => $days . ' days',
			'data'      => $trend_rows,
			'trend'     => self::calculate_trend( $trend_rows ),
		);
	}

	/**
	 * Calculate trend direction
	 *
	 * @since 1.6093.1200
	 * @param  array $snapshots Snapshot data.
	 * @return string Trend direction (improving, declining, stable).
	 */
	private static function calculate_trend( $snapshots ) {
		if ( count( $snapshots ) < 2 ) {
			return 'insufficient_data';
		}
		
		$first = reset( $snapshots )['findings_count'];
		$last = end( $snapshots )['findings_count'];
		
		$change_percent = ( ( $last - $first ) / max( $first, 1 ) ) * 100;
		
		if ( $change_percent < -10 ) {
			return 'improving';
		} elseif ( $change_percent > 10 ) {
			return 'declining';
		}
		
		return 'stable';
	}

	/**
	 * Delete old snapshots
	 *
	 * @since 1.6093.1200
	 * @param  int $days Days to keep snapshots.
	 * @return int Number of snapshots deleted.
	 */
	public static function cleanup_old_snapshots( $days = 90 ) {
		$cutoff_time = strtotime( "-{$days} days" );
		$deleted     = 0;
		$report_ids  = get_option( self::OPTION_REPORTS, array() );

		if ( ! is_array( $report_ids ) ) {
			return 0;
		}

		foreach ( $report_ids as $report_id ) {
			$report_id     = sanitize_key( (string) $report_id );
			$snapshot_ids  = self::get_report_snapshot_ids( $report_id );
			$remaining_ids = array();

			foreach ( $snapshot_ids as $snapshot_id ) {
				$snapshot = self::get_snapshot_by_id( (int) $snapshot_id );
				if ( null === $snapshot ) {
					continue;
				}

				$created_time = strtotime( $snapshot['created_at'] );
				if ( false !== $created_time && $created_time < $cutoff_time ) {
					delete_option( self::get_snapshot_option_key( (int) $snapshot_id ) );
					$deleted++;
					continue;
				}

				$remaining_ids[] = (int) $snapshot_id;
			}

			self::set_report_snapshot_ids( $report_id, $remaining_ids );
		}

		return $deleted;
	}

	/**
	 * Create snapshots table if it doesn't exist
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function maybe_create_table() {
		if ( false === get_option( self::OPTION_NEXT_ID, false ) ) {
			update_option( self::OPTION_NEXT_ID, 1, false );
		}

		if ( false === get_option( self::OPTION_REPORTS, false ) ) {
			update_option( self::OPTION_REPORTS, array(), false );
		}
	}

	/**
	 * Get the next snapshot ID.
	 *
	 * @since 1.6093.1200
	 * @return int Next ID.
	 */
	private static function get_next_snapshot_id() {
		$next_id = (int) get_option( self::OPTION_NEXT_ID, 1 );
		if ( $next_id < 1 ) {
			$next_id = 1;
		}

		update_option( self::OPTION_NEXT_ID, $next_id + 1, false );

		return $next_id;
	}

	/**
	 * Get option key for a single snapshot record.
	 *
	 * @since 1.6093.1200
	 * @param  int $snapshot_id Snapshot ID.
	 * @return string Option key.
	 */
	private static function get_snapshot_option_key( $snapshot_id ) {
		return 'wpshadow_report_snapshot_' . absint( $snapshot_id );
	}

	/**
	 * Get option key for report snapshot index.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report ID.
	 * @return string Option key.
	 */
	private static function get_report_index_option_key( $report_id ) {
		return 'wpshadow_report_snapshot_ids_' . sanitize_key( $report_id );
	}

	/**
	 * Persist snapshot record.
	 *
	 * @since 1.6093.1200
	 * @param  array $snapshot Snapshot record.
	 * @return bool True on success.
	 */
	private static function save_snapshot_record( array $snapshot ) {
		return update_option( self::get_snapshot_option_key( (int) $snapshot['id'] ), $snapshot, false );
	}

	/**
	 * Read report snapshot IDs in newest-first order.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report ID.
	 * @return array Snapshot IDs.
	 */
	private static function get_report_snapshot_ids( $report_id ) {
		$ids = get_option( self::get_report_index_option_key( $report_id ), array() );

		if ( ! is_array( $ids ) ) {
			return array();
		}

		return array_values( array_map( 'absint', $ids ) );
	}

	/**
	 * Save report snapshot IDs in newest-first order.
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report ID.
	 * @param  array  $ids       Snapshot IDs.
	 * @return void
	 */
	private static function set_report_snapshot_ids( $report_id, array $ids ) {
		$normalized_ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		update_option( self::get_report_index_option_key( $report_id ), $normalized_ids, false );
	}
}
