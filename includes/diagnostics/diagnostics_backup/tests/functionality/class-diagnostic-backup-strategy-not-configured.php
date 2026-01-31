<?php
/**
 * Backup Strategy Not Configured Diagnostic
 *
 * Checks if automated backups are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Strategy Not Configured Diagnostic Class
 *
 * Detects missing backup configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Backup_Strategy_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-strategy-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Strategy Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$backup_plugins = array(
			'backwpup/backwpup.php',
			'wp-backups/wp-backups.php',
			'duplicator/duplicator.php',
			'jetpack/jetpack.php',
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
				'description'   => __( 'No backup plugin is active. Without backups, data loss from hacks, server failures, or human error cannot be recovered.', 'wpshadow' ),
				'severity'      => 'critical',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/backup-strategy-not-configured',
			);
		}

		return null;
	}
}
