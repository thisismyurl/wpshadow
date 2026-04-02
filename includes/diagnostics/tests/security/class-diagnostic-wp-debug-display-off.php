<?php
/**
 * WP Debug Display Off Diagnostic (Stub)
 *
 * TODO stub mapped to the security gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Wp_Debug_Display_Off Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Wp_Debug_Display_Off extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-debug-display-off';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP Debug Display Off';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for WP Debug Display Off';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check WP_DEBUG_DISPLAY state.
	 *
	 * TODO Fix Plan:
	 * - Disable frontend error output.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If WP_DEBUG itself is off, display can't leak anything.
		if ( ! Server_Env::is_wp_debug() ) {
			return null;
		}

		if ( ! Server_Env::is_wp_debug_display() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WP_DEBUG is enabled and WP_DEBUG_DISPLAY is set to true (or not explicitly disabled). PHP errors and warnings are being printed directly to the page, visible to all visitors. This leaks server paths, plugin names, and code structure to potential attackers.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/wp-debug-display-off',
			'details'      => array(
				'wp_debug'         => true,
				'wp_debug_display' => true,
				'fix'              => __( 'Add define( \'WP_DEBUG_DISPLAY\', false ); to wp-config.php while keeping WP_DEBUG if needed.', 'wpshadow' ),
			),
		);
	}
}
