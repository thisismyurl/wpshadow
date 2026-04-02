<?php
/**
 * Debug Mode Off in Production Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 16.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug Mode Off in Production Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Debug_Mode_Off_Production extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'debug-mode-off-production';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Debug Mode Off in Production';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Debug Mode Off in Production. TODO: implement full test and remediation guidance.';

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
	 * Use WP_DEBUG and environment checks.
	 *
	 * TODO Fix Plan:
	 * Fix by setting WP_DEBUG false in production.
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
		if ( ! Server_Env::is_wp_debug() ) {
			return null;
		}

		$issues = array(
			__( 'WP_DEBUG is set to true.', 'wpshadow' ),
		);

		$severity     = 'medium';
		$threat_level = 50;

		if ( Server_Env::is_wp_debug_display() ) {
			$issues[]     = __( 'WP_DEBUG_DISPLAY is on — PHP errors are printed to the page and visible to visitors.', 'wpshadow' );
			$severity     = 'high';
			$threat_level = 70;
		}

		if ( Server_Env::is_script_debug() ) {
			$issues[] = __( 'SCRIPT_DEBUG is on — unminified scripts are loaded on every page.', 'wpshadow' );
		}

		if ( Server_Env::is_savequeries() ) {
			$issues[] = __( 'SAVEQUERIES is on — all queries are stored in memory on every request.', 'wpshadow' );
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress debug mode is enabled on a site that appears to be in production. Debug mode can expose error messages, file paths, and query data to visitors, leaking information useful to attackers.', 'wpshadow' ),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/debug-mode-off-production',
			'details'      => array(
				'issues'            => $issues,
				'wp_debug'          => true,
				'wp_debug_display'  => Server_Env::is_wp_debug_display(),
				'script_debug'      => Server_Env::is_script_debug(),
				'savequeries'       => Server_Env::is_savequeries(),
			),
		);
	}
}
