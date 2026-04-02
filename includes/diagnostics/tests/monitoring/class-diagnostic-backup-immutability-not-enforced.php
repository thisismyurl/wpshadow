<?php
/**
 * Backup Immutability Not Enforced Diagnostic
 *
 * Checks if backup immutability (write-once storage) is configured.
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
 * Diagnostic_Backup_Immutability_Not_Enforced Class
 *
 * Detects when backups lack write-once (WORM) protection.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Immutability_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-immutability-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Immutability Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks backup immutability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'backup_immutable_mode' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your backups can be deleted or modified, leaving you vulnerable to ransomware attacks. Ransomware often targets backups to prevent recovery. Write-once (immutable) storage prevents attackers from destroying your safety net, even if they gain admin access to your server.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-immutability',
			);

			// Add upgrade path if Vault not active
			if ( ! Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
				$finding = Upgrade_Path_Helper::add_upgrade_path(
					$finding,
					'vault',
					'immutable-storage',
					'https://wpshadow.com/kb/manual-immutable-backups'
				);
			}

			return $finding;
		}

		return null;
	}
}
