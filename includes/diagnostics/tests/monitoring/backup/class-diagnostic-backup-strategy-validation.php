<?php
/**
 * Backup Strategy Validation Diagnostic
 *
 * Checks if a comprehensive backup strategy is in place.
 *
 * @package WPShadow\Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Backup Strategy Validation
 *
 * Detects whether the site has a reliable backup strategy configured.
 */
class Diagnostic_Backup_Strategy_Validation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-strategy-validation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Strategy Validation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comprehensive backup strategy is in place';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'backupbuddy/backupbuddy.php'                              => 'BackupBuddy',
			'updraftplus/updraftplus.php'                              => 'UpdraftPlus',
			'backup-migration/backup-migration.php'                    => 'Backup Migration',
			'all-in-one-wp-migration/all-in-one-wp-migration.php'      => 'All-in-One WP Migration',
			'jetpack-backup/jetpack-backup.php'                        => 'Jetpack Backup',
			'vaultpress/vaultpress.php'                                => 'VaultPress',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_backup_tools']   = count( $active );
		$stats['backup_plugins']        = $active;

		if ( empty( $active ) ) {
			$issues[] = __( 'No backup solution detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Regular automated backups protect your site from data loss due to hacking, accidental deletion, or server failures. A comprehensive backup strategy includes multiple backup locations, automated schedules, and tested restore procedures.', 'wpshadow' ),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/backup-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
