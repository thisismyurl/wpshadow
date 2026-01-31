<?php
/**
 * Critical Plugins Not Backed Up Diagnostic
 *
 * Checks if critical plugins are backed up.
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
 * Critical Plugins Not Backed Up Diagnostic Class
 *
 * Detects missing plugin backups.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Critical_Plugins_Not_Backed_Up extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-plugins-not-backed-up';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Critical Plugins Not Backed Up';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical plugins are backed up';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup of essential plugins
		if ( ! get_option( 'critical_plugins_backup_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Critical plugins are not backed up. Create backup snapshots of all active plugins before updates to quickly restore if needed.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/critical-plugins-not-backed-up',
			);
		}

		return null;
	}
}
