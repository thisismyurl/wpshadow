<?php
/**
 * Plugin Update Frequency Audit Trail Diagnostic
 *
 * Checks if plugin updates are tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Update Frequency Audit Trail Diagnostic Class
 *
 * Detects untracked plugin updates.
 *
 * @since 1.2601.2340
 */
class Diagnostic_Plugin_Update_Frequency_Audit_Trail extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-frequency-audit-trail';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Update Frequency Audit Trail';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugin updates are tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for audit trail plugins
		$audit_plugins = array(
			'activity-log/activity-log.php',
			'stream/stream.php',
		);

		$audit_active = false;
		foreach ( $audit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$audit_active = true;
				break;
			}
		}

		if ( ! $audit_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Plugin update history is not tracked. Enable audit logging to track all plugin updates and changes.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-update-frequency-audit-trail',
			);
		}

		return null;
	}
}
