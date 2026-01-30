<?php
/**
 * Report Annotation Manager
 *
 * Handles notes and comments on report findings.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since      1.2603.0200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Annotation_Manager Class
 *
 * Manages annotations on report findings.
 *
 * @since 1.2603.0200
 */
class Report_Annotation_Manager {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private static $table_name = 'wpshadow_report_annotations';

	/**
	 * Maybe create table
	 *
	 * @since  1.2603.0200
	 * @return void
	 */
	public static function maybe_create_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			report_id varchar(100) NOT NULL,
			finding_id varchar(100) NOT NULL,
			annotation_text longtext NOT NULL,
			action_taken varchar(50) DEFAULT NULL,
			status varchar(20) DEFAULT 'open',
			user_id bigint(20) DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY report_id (report_id),
			KEY finding_id (finding_id),
			KEY status (status)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add annotation
	 *
	 * @since  1.2603.0200
	 * @param  string $report_id Report ID.
	 * @param  string $finding_id Finding ID.
	 * @param  string $text Annotation text.
	 * @param  array  $options Additional options.
	 * @return int|false Annotation ID or false.
	 */
	public static function add_annotation( $report_id, $finding_id, $text, $options = array() ) {
		global $wpdb;
		
		self::maybe_create_table();
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		$data = array(
			'report_id'       => sanitize_key( $report_id ),
			'finding_id'      => sanitize_key( $finding_id ),
			'annotation_text' => wp_kses_post( $text ),
			'action_taken'    => isset( $options['action_taken'] ) ? sanitize_text_field( $options['action_taken'] ) : null,
			'status'          => isset( $options['status'] ) ? sanitize_key( $options['status'] ) : 'open',
			'user_id'         => get_current_user_id(),
			'created_at'      => current_time( 'mysql' ),
		);
		
		$result = $wpdb->insert( $table_name, $data );
		
		if ( $result ) {
			$annotation_id = $wpdb->insert_id;
			
			/**
			 * Fires after annotation is added.
			 *
			 * @since 1.2603.0200
			 *
			 * @param int    $annotation_id Annotation ID.
			 * @param string $report_id Report ID.
			 * @param string $finding_id Finding ID.
			 */
			do_action( 'wpshadow_after_annotation_added', $annotation_id, $report_id, $finding_id );
			
			return $annotation_id;
		}
		
		return false;
	}

	/**
	 * Get annotations for finding
	 *
	 * @since  1.2603.0200
	 * @param  string $report_id Report ID.
	 * @param  string $finding_id Finding ID.
	 * @return array Annotations.
	 */
	public static function get_annotations( $report_id, $finding_id ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE report_id = %s AND finding_id = %s ORDER BY created_at DESC",
				$report_id,
				$finding_id
			),
			ARRAY_A
		);
		
		return $results ? $results : array();
	}

	/**
	 * Update annotation status
	 *
	 * @since  1.2603.0200
	 * @param  int    $annotation_id Annotation ID.
	 * @param  string $status New status.
	 * @return bool Success.
	 */
	public static function update_status( $annotation_id, $status ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		$result = $wpdb->update(
			$table_name,
			array(
				'status'     => sanitize_key( $status ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => absint( $annotation_id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		
		return $result !== false;
	}

	/**
	 * Get all annotations for report
	 *
	 * @since  1.2603.0200
	 * @param  string $report_id Report ID.
	 * @return array Annotations grouped by finding.
	 */
	public static function get_report_annotations( $report_id ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE report_id = %s ORDER BY created_at DESC",
				$report_id
			),
			ARRAY_A
		);
		
		// Group by finding_id
		$grouped = array();
		foreach ( $results as $annotation ) {
			$finding_id = $annotation['finding_id'];
			if ( ! isset( $grouped[ $finding_id ] ) ) {
				$grouped[ $finding_id ] = array();
			}
			$grouped[ $finding_id ][] = $annotation;
		}
		
		return $grouped;
	}

	/**
	 * Delete annotation
	 *
	 * @since  1.2603.0200
	 * @param  int $annotation_id Annotation ID.
	 * @return bool Success.
	 */
	public static function delete_annotation( $annotation_id ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		
		$result = $wpdb->delete(
			$table_name,
			array( 'id' => absint( $annotation_id ) ),
			array( '%d' )
		);
		
		return $result !== false;
	}
}
