<?php
/**
 * Backup Encryption Status Diagnostic
 *
 * Checks whether backups are encrypted at rest.
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
 * Diagnostic_Backup_Encryption_Status Class
 *
 * Detects if backup encryption is enabled in common backup plugins.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Encryption_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-encryption-status';

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
	protected static $description = 'Checks whether backups are encrypted at rest';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_backup = is_plugin_active( 'updraftplus/updraftplus.php' )
			|| is_plugin_active( 'backwpup/backwpup.php' )
			|| is_plugin_active( 'jetpack/jetpack.php' );

		if ( ! $has_backup ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup plugin detected. Encryption status cannot be verified.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-encryption-status?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		$encryption_key = get_option( 'updraft_encryptionphrase', '' );
		if ( empty( $encryption_key ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup encryption is not enabled. Encrypt backups to meet compliance requirements.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-encryption-status?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}