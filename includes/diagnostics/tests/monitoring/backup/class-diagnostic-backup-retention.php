<?php
/**
 * Backup Retention Policy Diagnostic
 *
 * Checks if backup retention settings are appropriate.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Retention Policy Diagnostic Class
 *
 * Verifies backup retention policy allows recovery from past issues.
 * Like checking how long you keep old versions of important files.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Retention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-retention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Retention Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backup retention settings are appropriate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the backup retention policy diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if retention issues detected, null otherwise.
	 */
	public static function check() {
		$retention_count = null;
		$retention_days = null;

		// UpdraftPlus.
		if ( class_exists( 'UpdraftPlus' ) ) {
			$retain = get_option( 'updraft_retain', 2 );
			$retention_count = (int) $retain;
		}

		// BackWPup (checks first job only).
		if ( class_exists( 'BackWPup' ) && class_exists( 'BackWPup_Option' ) ) {
			$jobs = \BackWPup_Option::get_job_ids();
			if ( ! empty( $jobs ) ) {
				$first_job = $jobs[0];
				$max_backups = \BackWPup_Option::get( $first_job, 'maxbackups' );
				if ( $max_backups ) {
					$retention_count = (int) $max_backups;
				}
			}
		}

		// If retention unknown.
		if ( null === $retention_count && null === $retention_days ) {
			return array(
				'id'           => self::$slug . '-unknown',
				'title'        => __( 'Backup Retention Policy Unknown', 'wpshadow' ),
				'description'  => __( 'We couldn\'t determine how many backup copies you\'re keeping (like not knowing how many old versions of a document you save). Check your backup plugin settings to ensure you keep at least 7-14 backup copies. This lets you restore from an older backup if a problem went unnoticed for a while.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-retention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(),
			);
		}

		// Check if retention is too short.
		if ( null !== $retention_count ) {
			if ( $retention_count < 2 ) {
				return array(
					'id'           => self::$slug . '-too-few',
					'title'        => __( 'Keeping Too Few Backup Copies', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: current retention count */
						__( 'You\'re only keeping %d backup copy (like only having one save file for a video game). If that backup is corrupted or was created after a problem started, you have no older version to fall back on. Increase retention to at least 7-14 backups for better protection.', 'wpshadow' ),
						$retention_count
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-retention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'retention_count' => $retention_count,
					),
				);
			}

			if ( $retention_count < 7 ) {
				return array(
					'id'           => self::$slug . '-low',
					'title'        => __( 'Backup Retention Could Be Longer', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: current retention count */
						__( 'You\'re keeping %d backup copies (like having a few save points in a game). This is okay, but keeping 7-14 backups is better (like having save points from different days). Sometimes problems aren\'t discovered immediately, and having older backups lets you restore from before the issue started.', 'wpshadow' ),
						$retention_count
					),
					'severity'     => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-retention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'retention_count' => $retention_count,
					),
				);
			}

			// Warning if retention is excessively high (storage cost concern).
			if ( $retention_count > 60 ) {
				return array(
					'id'           => self::$slug . '-excessive',
					'title'        => __( 'Very High Backup Retention', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: current retention count */
						__( 'You\'re keeping %d backup copies (like saving every single version of a document forever). This uses a lot of storage space and may increase backup costs. Consider reducing to 14-30 copies to balance protection with storage efficiency, unless you have a specific compliance requirement for longer retention.', 'wpshadow' ),
						$retention_count
					),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-retention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'retention_count' => $retention_count,
					),
				);
			}
		}

		return null; // Retention policy is reasonable.
	}
}
