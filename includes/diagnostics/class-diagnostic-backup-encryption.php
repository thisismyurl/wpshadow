<?php declare(strict_types=1);
/**
 * Backup Encryption Diagnostic
 *
 * Philosophy: Data protection - encrypt backups at rest
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if backups are encrypted.
 */
class Diagnostic_Backup_Encryption {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check common backup plugins for encryption
		$backup_plugins_with_encryption = array(
			'updraftplus/updraftplus.php' => 'updraft_encryptionphrase',
			'backwpup/backwpup.php' => 'backwpup_cfg_jobencryptionkey',
			'backup/backup.php' => 'backup_encryption_enabled',
		);
		
		$active = get_option( 'active_plugins', array() );
		$unencrypted_backups = array();
		
		foreach ( $backup_plugins_with_encryption as $plugin => $option_key ) {
			if ( in_array( $plugin, $active, true ) ) {
				// Check if encryption is configured
				$encryption_key = get_option( $option_key );
				if ( empty( $encryption_key ) ) {
					$plugin_name = explode( '/', $plugin )[0];
					$unencrypted_backups[] = ucfirst( $plugin_name );
				}
			}
		}
		
		if ( ! empty( $unencrypted_backups ) ) {
			return array(
				'id'          => 'backup-encryption',
				'title'       => 'Backups Not Encrypted',
				'description' => sprintf(
					'Your backup plugin (%s) is not configured to encrypt backups. Unencrypted backups stored in cloud services expose your database credentials, user data, and site content. Enable backup encryption.',
					implode( ', ', $unencrypted_backups )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/encrypt-wordpress-backups/',
				'training_link' => 'https://wpshadow.com/training/backup-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
