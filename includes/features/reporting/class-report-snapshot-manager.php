<?php
/**
 * Report Snapshot Manager
 *
 * Handles saving and comparing historical report snapshots.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since      1.603.0200
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
 * @since 1.603.0200
 */
class Report_Snapshot_Manager {

	/**
	 * Save a report snapshot
	 *
	 * @since  1.603.0200
	 * @param  string $report_id Report identifier.
	 * @param  array  $data      Report data.
	 * @param  array  $metadata  Additional metadata.
	 * @return int|false Snapshot ID or false on failure.
	 */
	public static function save_snapshot( $report_id, $data, $metadata = array() ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wpshadow_report_snapshots';
		
		// Ensure table exists
		self::maybe_create_table();
		
		$snapshot_data = array(
			'report_id'   => sanitize_key( $report_id ),
			'data'        => wp_json_encode( $data ),
			'metadata'    => wp_json_encode( $metadata ),
			'created_at'  => current_time( 'mysql' ),
			'findings_count' => isset( $data['findings'] ) ? count( $data['findings'] ) : 0,
		);
		
		$result = $wpdb->insert(
			$table_name,
			$snapshot_data,
			array( '%s', '%s', '%s', '%s', '%d' )
		);
		
		if ( $result ) {
			/**
			 * Fires after a report snapshot is saved.
			 *
			 * @since 1.603.0200
			 *
			 * @param int    $snapshot_id Snapshot ID.
			 * @param string $report_id Report ID.
			 * @param array  $data Report data.
			 */
			do_action( 'wpshadow_after_snapshot_saved', $wpdb->insert_id, $report_id, $data );
			
			return $wpdb->insert_id;
		}
		
		return false;
	}

	/**
	 * Get snapshots for a report
	 *
	 * @since  1.603.0200
	 * @param  string $report_id Report identifier.
	 * @param  int    $limit     Number of snapshots to retrieve.
	 * @return array Snapshots.
	 */
	public static function get_snapshots( $report_id, $limit = 10 ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wpshadow_report_snapshots';
		
		$snapshots = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE report_id = %s ORDER BY created_at DESC LIMIT %d",
				$report_id,
				$limit
			),
			ARRAY_A
		);
		
		// Decode JSON data
		foreach ( $snapshots as &$snapshot ) {
			$snapshot['data'] = json_decode( $snapshot['data'], true );
			$snapshot['metadata'] = json_decode( $snapshot['metadata'], true );
		}
		
		return $snapshots;
	}

	/**
	 * Compare two snapshots
	 *
	 * @since  1.603.0200
	 * @param  int $snapshot_id_1 First snapshot ID.
	 * @param  int $snapshot_id_2 Second snapshot ID.
	 * @return array Comparison data.
	 */
	public static function compare_snapshots( $snapshot_id_1, $snapshot_id_2 ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wpshadow_report_snapshots';
		
		$snapshot1 = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $snapshot_id_1 ),
			ARRAY_A
		);
		
		$snapshot2 = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $snapshot_id_2 ),
			ARRAY_A
		);
		
		if ( ! $snapshot1 || ! $snapshot2 ) {
			return array( 'error' => 'Snapshots not found' );
		}
		
		$data1 = json_decode( $snapshot1['data'], true );
		$data2 = json_decode( $snapshot2['data'], true );
		
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
	 * @since  1.603.0200
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
	 * @since  1.603.0200
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
	 * @since  1.603.0200
	 * @param  string $report_id Report identifier.
	 * @param  int    $days      Number of days to analyze.
	 * @return array Trend data.
	 */
	public static function get_trend_data( $report_id, $days = 30 ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wpshadow_report_snapshots';
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		
		$snapshots = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT created_at, findings_count FROM {$table_name} 
				WHERE report_id = %s AND created_at >= %s 
				ORDER BY created_at ASC",
				$report_id,
				$cutoff_date
			),
			ARRAY_A
		);
		
		return array(
			'report_id' => $report_id,
			'period'    => $days . ' days',
			'data'      => $snapshots,
			'trend'     => self::calculate_trend( $snapshots ),
		);
	}

	/**
	 * Calculate trend direction
	 *
	 * @since  1.603.0200
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
	 * @since  1.603.0200
	 * @param  int $days Days to keep snapshots.
	 * @return int Number of snapshots deleted.
	 */
	public static function cleanup_old_snapshots( $days = 90 ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wpshadow_report_snapshots';
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE created_at < %s",
				$cutoff_date
			)
		);
		
		return $deleted;
	}

	/**
	 * Create snapshots table if it doesn't exist
	 *
	 * @since  1.603.0200
	 * @return void
	 */
	private static function maybe_create_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'wpshadow_report_snapshots';
		$charset_collate = $wpdb->get_charset_collate();
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
			return;
		}
		
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			report_id varchar(100) NOT NULL,
			data longtext NOT NULL,
			metadata longtext,
			findings_count int(11) DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY report_id (report_id),
			KEY created_at (created_at)
		) {$charset_collate};";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
