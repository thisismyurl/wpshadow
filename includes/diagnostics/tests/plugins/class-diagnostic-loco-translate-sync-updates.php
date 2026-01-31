<?php
/**
 * Loco Translate Sync Updates Diagnostic
 *
 * Loco Translate Sync Updates misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1168.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loco Translate Sync Updates Diagnostic Class
 *
 * @since 1.1168.0000
 */
class Diagnostic_LocoTranslateSyncUpdates extends Diagnostic_Base {

	protected static $slug = 'loco-translate-sync-updates';
	protected static $title = 'Loco Translate Sync Updates';
	protected static $description = 'Loco Translate Sync Updates misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'LOCO_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Auto sync enabled
		$auto_sync = get_option( 'loco_auto_sync_enabled', 0 );
		if ( ! $auto_sync ) {
			$issues[] = 'Automatic sync not enabled';
		}

		// Check 2: Sync schedule configured
		$sync_schedule = get_option( 'loco_sync_schedule', '' );
		if ( empty( $sync_schedule ) ) {
			$issues[] = 'Sync schedule not configured';
		}

		// Check 3: Update on deploy
		$update_deploy = get_option( 'loco_update_on_deploy', 0 );
		if ( ! $update_deploy ) {
			$issues[] = 'Update on deployment not enabled';
		}

		// Check 4: Backup translations
		$backup = get_option( 'loco_backup_translations', 0 );
		if ( ! $backup ) {
			$issues[] = 'Translation backups not enabled';
		}

		// Check 5: Conflict resolution
		$conflict_res = get_option( 'loco_conflict_resolution', '' );
		if ( empty( $conflict_res ) ) {
			$issues[] = 'Conflict resolution not configured';
		}

		// Check 6: Sync notifications
		$notify = get_option( 'loco_sync_notifications', 0 );
		if ( ! $notify ) {
			$issues[] = 'Sync notifications not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Loco Translate sync issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/loco-translate-sync-updates',
			);
		}

		return null;
	}
}
