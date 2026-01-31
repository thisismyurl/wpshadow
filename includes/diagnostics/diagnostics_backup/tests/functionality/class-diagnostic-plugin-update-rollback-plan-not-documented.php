<?php
/**
 * Plugin Update Rollback Plan Not Documented Diagnostic
 *
 * Checks if rollback plan for plugin updates is documented.
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
 * Plugin Update Rollback Plan Not Documented Diagnostic Class
 *
 * Detects missing rollback documentation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Plugin_Update_Rollback_Plan_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-rollback-plan-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Update Rollback Plan Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if rollback plan is documented';

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
		// Check for backup plugins that enable rollback
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
				'description'   => __( 'No backup/rollback system is in place. Before major updates, ensure you can revert changes if needed.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-update-rollback-plan-not-documented',
			);
		}

		return null;
	}
}
