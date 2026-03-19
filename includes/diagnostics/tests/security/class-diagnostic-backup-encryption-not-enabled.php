<?php
/**
 * Backup Encryption Not Enabled Diagnostic
 *
 * Checks if backup encryption is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Encryption_Not_Enabled Class
 *
 * Detects when backups are not encrypted at rest.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Encryption_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-encryption-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Encryption Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks backup encryption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'backup_encryption_enabled' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Adding encryption to your backups provides an extra layer of protection (like putting your backup files in a safe). If someone gains access to your backup files, they won\'t be able to read customer information like emails and addresses. This is especially helpful for stores handling customer orders.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-encryption',
			);

			// Add upgrade path if Vault not active
			if ( ! Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
				$finding = Upgrade_Path_Helper::add_upgrade_path(
					$finding,
					'vault',
					'automatic-encryption',
					'https://wpshadow.com/kb/manual-backup-encryption'
				);
			}

			return $finding;
		}

		return null;
	}
}
