<?php
/**
 * Backup Encryption Status
 *
 * Checks if backups are encrypted at rest to protect sensitive data
 * from unauthorized access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6029.1107
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Encryption Status Diagnostic Class
 *
 * Verifies that backups are encrypted for data protection.
 *
 * @since 1.6029.1107
 */
class Diagnostic_Backup_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-encryption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Encryption Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are encrypted (data protection)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1107
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_backup_encryption_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$encryption_status = self::check_backup_encryption();

		if ( $encryption_status['encrypted'] ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Backups are not encrypted. Sensitive data may be exposed if backups are accessed.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/backup-encryption',
			'meta'         => array(
				'encrypted'     => false,
				'backup_plugin' => $encryption_status['backup_plugin'],
			),
			'details'      => array(
				__( 'Unencrypted backups expose sensitive data', 'wpshadow' ),
				__( 'Database backups may contain passwords and personal information', 'wpshadow' ),
				__( 'Encryption protects against unauthorized access', 'wpshadow' ),
			),
			'recommendation' => __( 'Enable backup encryption in your backup plugin settings or use a service with built-in encryption.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Check backup encryption.
	 *
	 * @since  1.6029.1107
	 * @return array Encryption status.
	 */
	private static function check_backup_encryption() {
		// Check for backup plugins and their encryption settings.
		$backup_plugins = array(
			'updraftplus/updraftplus.php' => 'updraft_encryptionphrase',
			'backwpup/backwpup.php'       => 'backwpup_cfg_showadminbar',
			'backup/backup.php'           => null, // Jetpack encrypts by default.
		);

		foreach ( $backup_plugins as $plugin => $encryption_option ) {
			if ( is_plugin_active( $plugin ) ) {
				// Jetpack Backup encrypts by default.
				if ( 'backup/backup.php' === $plugin ) {
					return array(
						'encrypted'     => true,
						'backup_plugin' => $plugin,
					);
				}

				// Check if encryption is configured.
				if ( $encryption_option ) {
					$encryption_key = get_option( $encryption_option, '' );
					if ( ! empty( $encryption_key ) ) {
						return array(
							'encrypted'     => true,
							'backup_plugin' => $plugin,
						);
					}
				}

				return array(
					'encrypted'     => false,
					'backup_plugin' => $plugin,
				);
			}
		}

		return array(
			'encrypted'     => false,
			'backup_plugin' => null,
		);
	}
}
