<?php
/**
 * Backup Restore Tested Diagnostic
 *
 * Tests if backups are actually tested and restorable.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Restore Tested Diagnostic Class
 *
 * Verifies that a restore test has been recorded.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tests_Backup_Restoration extends Diagnostic_Base {

	protected static $slug = 'tests-backup-restoration';
	protected static $title = 'Backup Restore Tested';
	protected static $description = 'Tests if backups are actually tested and restorable';
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$last_test = (int) get_option( 'wpshadow_last_backup_restore_test' );
		if ( $last_test ) {
			$days = floor( ( time() - $last_test ) / DAY_IN_SECONDS );
			if ( $days <= 90 ) {
				return null;
			}
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days */
					__( 'Last backup restore test was %d days ago. Test restores quarterly to confirm backups work.', 'wpshadow' ),
					$days
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-restore-tested?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'persona'      => 'enterprise-corp',
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No backup restore test recorded. Test restores to ensure backups are usable during emergencies.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/backup-restore-tested?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'enterprise-corp',
		);
	}
}
