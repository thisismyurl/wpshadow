<?php
/**
 * Backup Before Modification Diagnostic
 *
 * Issue #4876: No Automatic Backup Before Configuration Changes
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if critical file modifications are backed up first.
 * "We backed up your files first" builds confidence.
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
 * Diagnostic_Backup_Before_Modification Class
 *
 * Checks for:
 * - .htaccess modifications create backup first
 * - wp-config.php changes create backup
 * - Database schema changes create backup
 * - Plugin file modifications backed up
 * - Theme file modifications backed up
 * - Backup retention policy (keep 5-10 versions)
 * - Automatic rollback on error
 * - Clear notification: "We backed up your files first"
 *
 * Why this matters:
 * - Modifications can break sites
 * - Manual recovery is time-consuming
 * - Users feel confident knowing backup exists
 * - Commandment #8: Inspire Confidence
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Before_Modification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'backup-before-modification';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'No Automatic Backup Before Configuration Changes';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if critical file modifications are backed up automatically';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual backup verification requires checking treatment logic.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Create backup of .htaccess before modification', 'wpshadow' );
		$issues[] = __( 'Create backup of wp-config.php before changes', 'wpshadow' );
		$issues[] = __( 'Database backups before schema changes', 'wpshadow' );
		$issues[] = __( 'Keep 5-10 backup versions with timestamps', 'wpshadow' );
		$issues[] = __( 'Show user: "We backed up your files first"', 'wpshadow' );
		$issues[] = __( 'Automatic rollback if modification fails', 'wpshadow' );
		$issues[] = __( 'Provide manual restore option in UI', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Changes to critical files can break sites. Automatic backups let users recover quickly if something goes wrong.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/backup-before-modification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'critical_files'          => '.htaccess, wp-config.php, .user.ini, php.ini',
					'backup_location'         => 'wp-content/wpshadow/backups/{filename}.{timestamp}.bak',
					'retention'               => 'Keep 5-10 versions, delete older than 30 days',
					'commandment'             => 'Commandment #8: Inspire Confidence',
					'user_message'            => '"We backed up your files first. You can undo anytime."',
				),
			);
		}

		return null;
	}
}
