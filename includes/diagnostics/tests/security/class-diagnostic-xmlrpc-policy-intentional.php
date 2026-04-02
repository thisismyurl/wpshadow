<?php
/**
 * XML-RPC Policy Intentional Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 19.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Policy Intentional Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Xmlrpc_Policy_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'xmlrpc-policy-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Policy Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for XML-RPC Policy Intentional. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use apply_filters('xmlrpc_enabled', true) and endpoint response checks.
	 *
	 * TODO Fix Plan:
	 * Fix by disabling or protecting XML-RPC based on need.
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
		// xmlrpc.php does not exist — completely removed from this install (hardened).
		if ( ! file_exists( ABSPATH . 'xmlrpc.php' ) ) {
			return null;
		}

		// A plugin or theme has disabled xmlrpc via the standard WordPress filter.
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );
		if ( ! $xmlrpc_enabled ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'XML-RPC is enabled and accessible. This legacy API is rarely needed on modern WordPress sites and is a frequent target for brute-force, credential stuffing, and DDoS amplification attacks. Disable it unless you specifically require it for Jetpack, mobile app publishing, or a third-party integration.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/xmlrpc-policy',
			'details'      => array(
				'xmlrpc_file_exists' => true,
				'filter_disabled'    => false,
				'fix'                => __( 'Install a security plugin that disables XML-RPC, or add a server-level rule to block requests to xmlrpc.php.', 'wpshadow' ),
			),
		);
	}
}
