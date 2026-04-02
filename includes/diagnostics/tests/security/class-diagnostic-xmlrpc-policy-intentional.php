<?php
/**
 * XML-RPC Policy Intentional Diagnostic
 *
 * Checks whether XML-RPC is intentionally enabled, as an unmanaged
 * xmlrpc.php endpoint is a common vector for brute-force and amplification attacks.
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
 * XML-RPC Policy Intentional Diagnostic Class
 *
 * Verifies that xmlrpc.php has been removed or disabled via the xmlrpc_enabled
 * filter, flagging installations where the endpoint is still accessible.
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
	protected static $description = 'Checks whether XML-RPC is intentionally enabled, as an unmanaged xmlrpc.php endpoint is a common vector for brute-force and amplification attacks.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Confirms xmlrpc.php exists on disk, then checks whether the xmlrpc_enabled
	 * filter has been set to false by a plugin; returns a medium-severity finding
	 * when the endpoint is present and not explicitly disabled.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when XML-RPC is accessible, null when healthy.
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
			'kb_link'      => 'https://wpshadow.com/kb/xmlrpc-policy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'xmlrpc_file_exists' => true,
				'filter_disabled'    => false,
				'fix'                => __( 'Install a security plugin that disables XML-RPC, or add a server-level rule to block requests to xmlrpc.php.', 'wpshadow' ),
			),
		);
	}
}
