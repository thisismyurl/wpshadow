<?php
/**
 * Plugin Auto Updates Diagnostic
 *
 * Checks whether automatic plugin updates are enabled to ensure security
 * patches are applied promptly without requiring manual action.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Auto_Updates Class
 *
 * Verifies that WordPress automatic background updates for plugins are enabled,
 * flagging sites where auto-updates have been disabled or were never turned on.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Auto_Updates extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-auto-updates';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Auto Updates';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether automatic plugin updates are enabled to ensure security patches are applied promptly without requiring manual action.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Delegates to the WP_Settings helper to check whether automatic plugin
	 * updates are enabled across the site, returning a low-severity finding
	 * when they are not.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when auto-updates are disabled, null when healthy.
	 */
	public static function check() {
		if ( WP_Settings::is_plugin_auto_update_enabled() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Plugin auto-updates are not enabled. Sites relying on manual review cycles frequently fall behind on security patches. Enable plugin auto-updates or document and enforce a regular manual update schedule.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-auto-updates?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'plugin_auto_updates_enabled' => false,
				'theme_auto_updates_enabled'  => WP_Settings::is_theme_auto_update_enabled(),
			),
		);
	}
}
