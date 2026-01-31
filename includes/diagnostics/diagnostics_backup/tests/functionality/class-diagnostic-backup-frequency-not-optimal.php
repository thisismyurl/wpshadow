<?php
/**
 * Backup Frequency Not Optimal Diagnostic
 *
 * Checks if backups are scheduled frequently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Frequency Not Optimal Diagnostic Class
 *
 * Detects infrequent backups.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Backup_Frequency_Not_Optimal extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-frequency-not-optimal';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Frequency Not Optimal';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks backup frequency';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$backup_plugins = array(
			'backwpup/backwpup.php',
			'updraftplus/updraftplus.php',
			'jetpack-backup/jetpack-backup.php',
		);

		$backup_active = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$backup_active = true;
				break;
			}
		}

		if ( ! $backup_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No automated backup solution is active. Schedule daily backups to protect against data loss.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/backup-frequency-not-optimal',
			);
		}

		return null;
	}
}
