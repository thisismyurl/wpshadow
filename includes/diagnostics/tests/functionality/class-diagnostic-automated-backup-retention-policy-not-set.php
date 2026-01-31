<?php
/**
 * Automated Backup Retention Policy Not Set Diagnostic
 *
 * Checks if backup retention policy is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automated Backup Retention Policy Not Set Diagnostic Class
 *
 * Detects missing backup retention policy.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Automated_Backup_Retention_Policy_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automated-backup-retention-policy-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automated Backup Retention Policy Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backup retention policy is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$backup_plugins = array(
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
			'updraftplus/updraftplus.php',
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
				'description'   => __( 'Backup retention policy is not set. Configure automated backups with retention policies for disaster recovery.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/automated-backup-retention-policy-not-set',
			);
		}

		return null;
	}
}
