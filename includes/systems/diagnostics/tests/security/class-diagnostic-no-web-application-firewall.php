<?php
/**
 * No Web Application Firewall Diagnostic
 *
 * Detects when WAF is not configured,
 * allowing common attack patterns through.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Web Application Firewall
 *
 * Checks whether a Web Application Firewall (WAF)
 * is configured to block attack patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Web_Application_Firewall extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-web-application-firewall';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Web Application Firewall (WAF)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WAF is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for WAF plugins
		$has_waf = is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'sucuri-scanner/sucuri.php' ) ||
			is_plugin_active( 'ninjafirewall/ninjafirewall.php' );

		// Check for Cloudflare (common external WAF)
		$homepage = wp_remote_head( home_url() );
		$has_cloudflare = false;
		if ( ! is_wp_error( $homepage ) ) {
			$headers = wp_remote_retrieve_headers( $homepage );
			$has_cloudflare = isset( $headers['cf-ray'] ) || isset( $headers['server'] ) && strpos( $headers['server'], 'cloudflare' ) !== false;
		}

		if ( ! $has_waf && ! $has_cloudflare ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'A Web Application Firewall isn\'t configured, which means common attack patterns reach your site. WAFs block: SQL injection attempts, XSS (cross-site scripting) attacks, malicious bots, known exploit patterns. Think of WAF as airport security—screens requests before they reach your site. Good WAFs block 90%+ of automated attacks before they even touch WordPress. Available as plugin or service (Cloudflare, Sucuri).',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Attack Blocking',
					'potential_gain' => 'Block 90%+ of automated attacks',
					'roi_explanation' => 'WAFs stop attacks before they reach WordPress, blocking 90%+ of automated exploit attempts.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/web-application-firewall',
			);
		}

		return null;
	}
}
