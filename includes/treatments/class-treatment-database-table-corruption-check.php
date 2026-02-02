<?php
/**
 * Treatment for Database Table Corruption
 *
 * Repairs corrupted database tables using REPAIR TABLE command.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2603.1345
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Database_Table_Corruption_Check Class
 *
 * Repairs database tables identified as corrupt.
 *
 * @since 1.2603.1345
 */
class Treatment_Database_Table_Corruption_Check extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since  1.2603.1345
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'database-table-corruption-check';
	}

	/**
	 * Apply the treatment.
	 *
	 * Runs REPAIR TABLE on all corrupted tables identified by the diagnostic.
	 *
	 * @since  1.2603.1345
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about the operation.
	 * }
	 */
	public static function apply() {
		global $wpdb;

		// Get the finding details to know which tables are corrupt.
		$finding = wpshadow_get_finding( self::get_finding_id() );
		
		if ( empty( $finding ) || empty( $finding['details']['corrupt_tables'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'No corrupt tables found to repair', 'wpshadow' ),
			);
		}

		$corrupt_tables = $finding['details']['corrupt_tables'];
		$repaired = array();
		$failed = array();

		foreach ( $corrupt_tables as $table_info ) {
			$table = $table_info['table'];
			
			// Attempt to repair the table.
			$results = $wpdb->get_results( "REPAIR TABLE {$table}", ARRAY_A );
			
			if ( empty( $results ) ) {
				$failed[] = array(
					'table' => $table,
					'error' => __( 'No response from REPAIR TABLE command', 'wpshadow' ),
				);
				continue;
			}

			$repair_success = false;
			foreach ( $results as $row ) {
				if ( isset( $row['Msg_text'] ) && 
					( stripos( $row['Msg_text'], 'OK' ) !== false || 
					  stripos( $row['Msg_text'], 'repaired' ) !== false ) ) {
					$repair_success = true;
					break;
				}
			}

			if ( $repair_success ) {
				$repaired[] = $table;
			} else {
				$failed[] = array(
					'table' => $table,
					'error' => isset( $results[0]['Msg_text'] ) ? $results[0]['Msg_text'] : __( 'Unknown error', 'wpshadow' ),
				);
			}
		}

		// Determine overall success.
		if ( ! empty( $repaired ) && empty( $failed ) ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %d: number of repaired tables */
					_n(
						'Successfully repaired %d database table',
						'Successfully repaired %d database tables',
						count( $repaired ),
						'wpshadow'
					),
					count( $repaired )
				),
				'details' => array(
					'repaired_tables' => $repaired,
				),
			);
		}

		if ( ! empty( $repaired ) && ! empty( $failed ) ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of repaired tables, 2: number of failed tables */
					__( 'Repaired %1$d tables, but %2$d tables could not be repaired. Manual intervention may be required.', 'wpshadow' ),
					count( $repaired ),
					count( $failed )
				),
				'details' => array(
					'repaired_tables' => $repaired,
					'failed_repairs' => $failed,
				),
			);
		}

		return array(
			'success' => false,
			'message' => sprintf(
				/* translators: %d: number of failed tables */
				_n(
					'Failed to repair %d table. Manual database repair may be required.',
					'Failed to repair %d tables. Manual database repair may be required.',
					count( $failed ),
					'wpshadow'
				),
				count( $failed )
			),
			'details' => array(
				'failed_repairs' => $failed,
			),
		);
	}
}
