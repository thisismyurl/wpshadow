<?php
/**
 * No Rollback on Failed Imports
 *
 * Checks for data rollback capability when imports fail.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Rollback_On_Failed_Imports Class
 *
 * Validates data rollback mechanisms for import failures.
 *
 * @since 1.6030.2148
 */
class Diagnostic_No_Rollback_On_Failed_Imports extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-rollback-failed-imports';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Rollback on Failure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates data rollback capability when imports fail';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests rollback mechanisms for import failures.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for pre-import backup
		if ( ! self::has_pre_import_backup() ) {
			$issues[] = __( 'No backup created before import operations', 'wpshadow' );
		}

		// 2. Check for transaction support
		if ( ! self::supports_transactions() ) {
			$issues[] = __( 'Database transactions not used for atomic operations', 'wpshadow' );
		}

		// 3. Check for change tracking
		if ( ! self::tracks_changes() ) {
			$issues[] = __( 'Changes during import not tracked for reversal', 'wpshadow' );
		}

		// 4. Check for rollback mechanism
		if ( ! self::has_rollback_mechanism() ) {
			$issues[] = __( 'No rollback functionality when import fails', 'wpshadow' );
		}

		// 5. Check for restoration verification
		if ( ! self::verifies_rollback() ) {
			$issues[] = __( 'Rollback success not verified after failure', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of rollback issues */
					__( '%d rollback mechanism issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 70,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/import-rollback-protection',
				'recommendations' => array(
					__( 'Create backup before import operations begin', 'wpshadow' ),
					__( 'Use database transactions for atomic operations', 'wpshadow' ),
					__( 'Track all changes made during import', 'wpshadow' ),
					__( 'Implement rollback functionality for failures', 'wpshadow' ),
					__( 'Verify data integrity after rollback', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for pre-import backup.
	 *
	 * @since  1.6030.2148
	 * @return bool True if backups created.
	 */
	private static function has_pre_import_backup() {
		// Check for backup hook
		if ( has_filter( 'wpshadow_create_pre_import_backup' ) ) {
			return true;
		}

		// Check for backup creation action
		if ( has_action( 'wpshadow_before_import' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for transaction support.
	 *
	 * @since  1.6030.2148
	 * @return bool True if transactions supported.
	 */
	private static function supports_transactions() {
		global $wpdb;

		// Check if database supports transactions
		$engine = $wpdb->get_var( "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() LIMIT 1" );

		// InnoDB supports transactions
		if ( 'InnoDB' === $engine ) {
			return true;
		}

		// Check for transaction hook
		if ( has_filter( 'wpshadow_use_db_transactions' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for change tracking.
	 *
	 * @since  1.6030.2148
	 * @return bool True if changes tracked.
	 */
	private static function tracks_changes() {
		// Check for change log table
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_import_changes';
		$change_table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $change_table ) {
			return true;
		}

		// Check for change tracking hook
		if ( has_filter( 'wpshadow_track_import_changes' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for rollback mechanism.
	 *
	 * @since  1.6030.2148
	 * @return bool True if rollback implemented.
	 */
	private static function has_rollback_mechanism() {
		// Check for rollback action
		if ( has_action( 'wpshadow_rollback_import' ) ) {
			return true;
		}

		// Check for restore function
		if ( has_filter( 'wpshadow_restore_from_import_backup' ) ) {
			return true;
		}

		// Check for change reversal
		if ( has_filter( 'wpshadow_reverse_import_changes' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for rollback verification.
	 *
	 * @since  1.6030.2148
	 * @return bool True if rollback verified.
	 */
	private static function verifies_rollback() {
		// Check for integrity verification hook
		if ( has_filter( 'wpshadow_verify_rollback_integrity' ) ) {
			return true;
		}

		// Check for data consistency check
		if ( has_filter( 'wpshadow_check_data_consistency_after_rollback' ) ) {
			return true;
		}

		return false;
	}
}
