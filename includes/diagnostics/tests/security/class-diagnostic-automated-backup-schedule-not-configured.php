<?php
/**
 * Automated Backup Schedule Not Configured - Diagnostic
 *
 * Verifies that automated backups are scheduled and running regularly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Automated_Backup_Schedule_Not_Configured Class
 *
 * Checks if automated backups are configured and running.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Automated_Backup_Schedule_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automated-backup-schedule-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automated Backup Schedule Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if automated backup schedule is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if backup schedule is configured
		if ( ! wp_next_scheduled( 'wpshadow_automated_backup' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automated backup schedule is not configured. Set up daily or weekly automatic backups to protect your site from data loss.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/automated-backup-schedule-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
