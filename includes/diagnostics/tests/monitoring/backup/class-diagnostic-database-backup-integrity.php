<?php
/**
 * Database Backup Integrity Diagnostic
 *
 * Verifies database backups are complete and usable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1615
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Backup Integrity Diagnostic Class
 *
 * Checks if database backups are complete and can be restored.
 * Like testing that your saved game files actually work.
 *
 * @since 1.6035.1615
 */
class Diagnostic_Database_Backup_Integrity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-integrity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Integrity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies database backups are complete and usable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the database backup integrity diagnostic check.
	 *
	 * @since  1.6035.1615
	 * @return array|null Finding array if backup integrity issues detected, null otherwise.
	 */
	public static function check() {
		// Check UpdraftPlus backup status.
		if ( class_exists( 'UpdraftPlus' ) ) {
			$last_backup = get_option( 'updraft_last_backup', array() );
			$backup_history = get_option( 'updraft_backup_history', array() );

			// Check if last database backup succeeded.
			$last_db_backup = $last_backup['db'] ?? 0;
			if ( $last_db_backup > 0 ) {
				// Check if backup exists in history.
				$backup_found = false;
				$backup_size = 0;
				
				foreach ( $backup_history as $timestamp => $backup_info ) {
					if ( abs( $timestamp - $last_db_backup ) < 10 ) {
						$backup_found = true;
						$backup_size = $backup_info['db'][0]['size'] ?? 0;
						break;
					}
				}

				// Check if backup size is suspiciously small.
				if ( $backup_size > 0 && $backup_size < 1024 ) {
					return array(
						'id'           => self::$slug . '-too-small',
						'title'        => __( 'Database Backup Suspiciously Small', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: %s: backup size */
							__( 'Your most recent database backup is only %s (like a save file that\'s way too small to contain all your data). This suggests the backup may be incomplete or corrupted. Run a test backup and verify it completes successfully in your backup plugin.', 'wpshadow' ),
							size_format( $backup_size )
						),
						'severity'     => 'high',
						'threat_level' => 75,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/backup-integrity',
						'context'      => array(
							'backup_size' => $backup_size,
						),
					);
				}
			}

			// Check for backup failures in logs.
			$last_log = get_option( 'updraft_last_backup_log', '' );
			if ( ! empty( $last_log ) && ( false !== strpos( $last_log, 'error' ) || false !== strpos( $last_log, 'failed' ) ) ) {
				return array(
					'id'           => self::$slug . '-errors-detected',
					'title'        => __( 'Backup Errors Detected in Logs', 'wpshadow' ),
					'description'  => __( 'Your backup logs show errors (like getting error messages when trying to save a file). This means recent backups may be incomplete or unusable. Check your backup plugin logs and resolve any errors. Common causes: insufficient disk space, database connection issues, or file permission problems.', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-integrity',
					'context'      => array(),
				);
			}
		}

		// Check BackWPup logs.
		if ( class_exists( 'BackWPup' ) && class_exists( 'BackWPup_Option' ) ) {
			$jobs = \BackWPup_Option::get_job_ids();
			foreach ( $jobs as $job_id ) {
				$last_run = \BackWPup_Option::get( $job_id, 'lastrun' );
				if ( $last_run ) {
					$errors = \BackWPup_Option::get( $job_id, 'lasterrors' );
					$warnings = \BackWPup_Option::get( $job_id, 'lastwarnings' );

					if ( $errors > 0 ) {
						return array(
							'id'           => self::$slug . '-backwpup-errors',
							'title'        => __( 'Backup Job Completed with Errors', 'wpshadow' ),
							'description'  => sprintf(
								/* translators: %d: number of errors */
								__( 'Your last backup job completed with %d errors (like a save operation that partially failed). The backup may be incomplete or corrupted. Check BackWPup logs to see what went wrong and fix the issues before relying on these backups.', 'wpshadow' ),
								$errors
							),
							'severity'     => 'high',
							'threat_level' => 75,
							'auto_fixable' => false,
							'kb_link'      => 'https://wpshadow.com/kb/backup-integrity',
							'context'      => array(
								'errors'   => $errors,
								'warnings' => $warnings,
							),
						);
					}
				}
			}
		}

		// Generic check: Verify a database backup test.
		$last_integrity_check = get_transient( 'wpshadow_backup_integrity_check' );
		if ( false === $last_integrity_check ) {
			// Suggest periodic integrity testing.
			return array(
				'id'           => self::$slug . '-not-tested',
				'title'        => __( 'Backup Integrity Not Tested Recently', 'wpshadow' ),
				'description'  => __( 'Testing your backups regularly ensures they actually work when you need them (like making sure your spare tire isn\'t flat). Download a backup and try restoring it to a test environment at least every few months. Many people discover their backups don\'t work only when disaster strikes. Set a calendar reminder to test your backups quarterly.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-integrity',
				'context'      => array(),
			);
		}

		return null; // Backup integrity appears good.
	}
}
