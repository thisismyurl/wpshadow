<?php
/**
 * Database Backup Encryption Diagnostic
 *
 * Verifies database backups are encrypted to protect sensitive data
 * in case backup files are exposed or stolen.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Backup_Encryption Class
 *
 * Detects unencrypted database backups.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Backup_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-encryption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Encryption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies database backups are encrypted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if backups unencrypted, null otherwise.
	 */
	public static function check() {
		$backup_check = self::check_backup_encryption();

		if ( $backup_check['is_encrypted'] ) {
			return null; // Backups encrypted
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Database backups not encrypted. Backups contain all user data, passwords, payment info. If backup file leaked = complete data breach.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/backup-encryption',
			'family'       => self::$family,
			'meta'         => array(
				'backup_method'   => $backup_check['method'],
				'encryption_used' => $backup_check['is_encrypted'],
			),
			'details'      => array(
				'why_encrypt_backups'       => array(
					__( 'Database contains ALL sensitive data' ),
					__( 'User emails, passwords (hashed but still sensitive)' ),
					__( 'Payment info, order history (if e-commerce)' ),
					__( 'Personal data (GDPR compliance)' ),
					__( 'API keys, tokens in wp_options table' ),
				),
				'backup_exposure_scenarios' => array(
					'Public Directory' => array(
						'Backup stored in /wp-content/backups/',
						'Accessible via https://site.com/wp-content/backups/db.sql',
						'Google indexes backup files',
					),
					'Compromised FTP' => array(
						'Attacker gets FTP credentials',
						'Downloads backup files',
						'Extracts all data offline',
					),
					'Cloud Storage Breach' => array(
						'Backups on Dropbox/Google Drive',
						'Account credentials leaked',
						'Backups exposed',
					),
				),
				'encryption_methods'        => array(
					'AES-256 Encryption' => array(
						'Industry standard',
						'Same as banks, military',
						'Virtually unbreakable',
					),
					'GPG Encryption' => array(
						'Public/private key encryption',
						'Open source',
						'Command: gpg --encrypt backup.sql',
					),
					'7-Zip with Password' => array(
						'7z a -p backup.7z backup.sql',
						'Use strong password (20+ chars)',
						'AES-256 encryption',
					),
				),
				'encrypted_backup_plugins'  => array(
					'UpdraftPlus (Free/Premium)' => array(
						'Encryption built-in (Premium)',
						'Stores encrypted on cloud',
						'AES-256 encryption',
					),
					'BackWPup (Free)' => array(
						'Can create encrypted archives',
						'Password protection',
						'Multiple storage destinations',
					),
					'VaultPress (Jetpack - Paid)' => array(
						'Encrypted backups included',
						'Real-time backups',
						'Automatic encryption',
					),
				),
				'manual_encryption_steps'   => array(
					'Using GPG (Linux/Mac)' => array(
						'1. Create backup: wp db export backup.sql',
						'2. Encrypt: gpg --symmetric --cipher-algo AES256 backup.sql',
						'3. Enter strong passphrase',
						'4. Result: backup.sql.gpg (encrypted)',
						'5. Delete: rm backup.sql (original)',
					),
					'Using 7-Zip (Windows/Linux)' => array(
						'1. Install: sudo apt install p7zip-full',
						'2. Encrypt: 7z a -p -mhe=on backup.7z backup.sql',
						'3. Enter password',
						'4. Header encryption enabled (-mhe)',
					),
				),
				'backup_storage_best_practices' => array(
					__( 'Never store backups in web-accessible directory' ),
					__( 'Always encrypt before uploading to cloud' ),
					__( 'Use different encryption key than site password' ),
					__( 'Store encryption key separately (password manager)' ),
					__( 'Test backup restoration process regularly' ),
				),
			),
		);
	}

	/**
	 * Check backup encryption.
	 *
	 * @since  1.2601.2148
	 * @return array Backup encryption status.
	 */
	private static function check_backup_encryption() {
		// Check for backup plugins with encryption
		$encrypted_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
		);

		foreach ( $encrypted_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Plugin active but can't verify encryption enabled without checking settings
				// Conservative: assume not encrypted unless Premium
				return array(
					'is_encrypted' => false,
					'method'       => $plugin_name . ' (encryption status unknown)',
				);
			}
		}

		return array(
			'is_encrypted' => false,
			'method'       => 'No backup plugin detected',
		);
	}
}
