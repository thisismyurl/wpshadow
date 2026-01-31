<?php
/**
 * Automatic Backup Scheduling Not Configured Diagnostic
 *
 * Checks if backups are scheduled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
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
 * @since 1.2601.2352
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
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugin
		if ( ! is_plugin_active( 'backwpup/backwpup.php' ) && ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automatic backup scheduling is not configured. Set up automatic daily backups to protect against data loss.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/automatic-backup-scheduling-not-configured',
			);
		}

		return null;
	}
}
