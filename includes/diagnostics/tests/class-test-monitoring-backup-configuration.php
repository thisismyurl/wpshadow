<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Backup Configuration (Monitoring)
 *
 * Checks if automated backups are configured
 * Philosophy: Show value (#9) - backups prevent data loss
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_BackupConfiguration extends Diagnostic_Base {


	public static function check(): ?array {
		// Check if backup plugin is active
		$plugins       = get_plugins();
		$backup_active = false;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if (
				stripos( $plugin_file, 'backup' ) !== false ||
				stripos( $plugin_file, 'jetpack' ) !== false ||
				stripos( $plugin_file, 'backwpup' ) !== false ||
				stripos( $plugin_file, 'updraft' ) !== false
			) {
				if ( is_plugin_active( $plugin_file ) ) {
					$backup_active = true;
					break;
				}
			}
		}

		if ( ! $backup_active ) {
			return array(
				'id'           => 'backup-configuration',
				'title'        => __( 'Automated backups not configured', 'wpshadow' ),
				'description'  => __( 'Enable automated backups to protect against data loss. Use UpdraftPlus, BackWPup, or similar.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
			);
		}

		return null;
	}

	public static function test_live_backup_configuration(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Automated backups are configured', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
