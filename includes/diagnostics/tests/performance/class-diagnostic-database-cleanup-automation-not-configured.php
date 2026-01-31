<?php
/**
 * Database Cleanup Automation Not Configured Diagnostic
 *
 * Checks if database cleanup is automated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Cleanup Automation Not Configured Diagnostic Class
 *
 * Detects missing database cleanup automation.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Database_Cleanup_Automation_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-cleanup-automation-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Cleanup Automation Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database cleanup is automated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for database optimization plugins
		$db_plugins = array(
			'wp-optimize/wp-optimize.php',
			'advanced-database-cleaner/advanced-database-cleaner.php',
		);

		$db_active = false;
		foreach ( $db_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$db_active = true;
				break;
			}
		}

		if ( ! $db_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database cleanup automation is not configured. Schedule database optimization to remove spam comments, orphaned data, and unused tables.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-cleanup-automation-not-configured',
			);
		}

		return null;
	}
}
