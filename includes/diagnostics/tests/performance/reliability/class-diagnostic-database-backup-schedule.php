<?php
/**
 * Database Backup Schedule Diagnostic
 *
 * Issue #4900: No Automated Database Backup Configured
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if automated database backups are scheduled.
 * Without backups, data loss is permanent.
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
 * Diagnostic_Database_Backup_Schedule Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Backup_Schedule extends Diagnostic_Base {

	protected static $slug = 'database-backup-schedule';
	protected static $title = 'No Automated Database Backup Configured';
	protected static $description = 'Checks if regular database backups are scheduled';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		// Check for common backup plugins
		$has_backup = false;
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'backup/backup.php',
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup = true;
				break;
			}
		}

		if ( ! $has_backup ) {
			$issues[] = __( 'Configure automated daily database backups', 'wpshadow' );
			$issues[] = __( 'Store backups off-site (not same server)', 'wpshadow' );
			$issues[] = __( 'Keep 30 days of backups minimum', 'wpshadow' );
			$issues[] = __( 'Test backup restoration regularly (quarterly)', 'wpshadow' );
			$issues[] = __( 'Encrypt backups containing sensitive data', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Disk failures, hacking, and human errors cause data loss. Without backups, data loss is permanent and unrecoverable.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/database-backups?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'backup_frequency'        => 'Daily for active sites, weekly for static',
					'retention'               => 'Keep 30 daily + 12 monthly backups',
					'plugins'                 => 'UpdraftPlus, BackWPup, WPShadow Vault',
				),
			);
		}

		return null;
	}
}
