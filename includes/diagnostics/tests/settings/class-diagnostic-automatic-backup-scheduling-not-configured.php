<?php
/**
 * Automatic Backup Scheduling Not Configured Diagnostic
 *
 * Checks if backups are scheduled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatic Backup Scheduling Not Configured Diagnostic Class
 *
 * Detects missing backup scheduling.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Automatic_Backup_Scheduling_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automatic-backup-scheduling-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Backup Scheduling Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are scheduled';

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
		// Check for backup plugin
		if ( ! is_plugin_active( 'backwpup/backwpup.php' ) && ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No automatic backup system is configured. Without scheduled backups, you risk losing all your content, customer data, and months of work if something goes wrong. The average cost of data loss for a small business: $3,000+ per hour of downtime.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/automatic-backup-scheduling',
			);

			// Add upgrade path if Vault not active
			if ( ! \WPShadow\Core\Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
				$finding = \WPShadow\Core\Upgrade_Path_Helper::add_upgrade_path(
					$finding,
					'vault',
					'scheduled-backups',
					'https://wpshadow.com/kb/manual-backup-scheduling'
				);
			}

			return $finding;
		}

		return null;
	}
}
