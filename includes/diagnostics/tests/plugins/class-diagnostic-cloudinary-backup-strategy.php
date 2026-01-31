<?php
/**
 * Cloudinary Backup Strategy Diagnostic
 *
 * Cloudinary Backup Strategy detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.786.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudinary Backup Strategy Diagnostic Class
 *
 * @since 1.786.0000
 */
class Diagnostic_CloudinaryBackupStrategy extends Diagnostic_Base {

	protected static $slug = 'cloudinary-backup-strategy';
	protected static $title = 'Cloudinary Backup Strategy';
	protected static $description = 'Cloudinary Backup Strategy detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'cloudinary_api_key', '' ) && ! get_option( 'cloudinary_enabled', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Backup strategy enabled
		$backup_strategy = get_option( 'cloudinary_backup_strategy_enabled', 0 );
		if ( ! $backup_strategy ) {
			$issues[] = 'Backup strategy not enabled';
		}

		// Check 2: Backup frequency configured
		$backup_freq = get_option( 'cloudinary_backup_frequency', '' );
		if ( empty( $backup_freq ) ) {
			$issues[] = 'Backup frequency not configured';
		}

		// Check 3: Local backup retention
		$retention = absint( get_option( 'cloudinary_backup_retention_days', 0 ) );
		if ( $retention <= 0 ) {
			$issues[] = 'Backup retention not configured';
		}

		// Check 4: Backup encryption
		$encryption = get_option( 'cloudinary_backup_encryption', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Backup encryption not enabled';
		}

		// Check 5: Restore testing
		$restore_test = get_option( 'cloudinary_backup_restore_testing', 0 );
		if ( ! $restore_test ) {
			$issues[] = 'Restore testing not enabled';
		}

		// Check 6: Backup notifications
		$notifications = get_option( 'cloudinary_backup_notifications', 0 );
		if ( ! $notifications ) {
			$issues[] = 'Backup notifications not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Cloudinary backup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudinary-backup-strategy',
			);
		}

		return null;
	}
}
