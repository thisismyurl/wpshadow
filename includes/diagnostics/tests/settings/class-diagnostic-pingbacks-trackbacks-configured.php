<?php
/**
 * Pingbacks and Trackbacks Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 26.
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
 * Pingbacks and Trackbacks Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Pingbacks_Trackbacks_Configured extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'pingbacks-trackbacks-configured';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Pingbacks and Trackbacks Configured';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Pingbacks and Trackbacks Configured. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check default_ping_status and default_pingback_flag options.
	 *
	 * TODO Fix Plan:
	 * Fix by disabling unless explicitly needed.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		if ( ! WP_Settings::are_pings_open_by_default() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Pingbacks and trackbacks are enabled by default for new posts. These features are rarely needed on modern sites and are frequently abused for link spam and DDoS amplification attacks. Disable them unless you have a specific use case.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/pingbacks-trackbacks',
			'details'      => array(
				'default_ping_status' => get_option( 'default_ping_status', 'open' ),
				'default_pingback_flag' => (bool) get_option( 'default_pingback_flag', 1 ),
			),
		);
	}
}
