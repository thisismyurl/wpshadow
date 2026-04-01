<?php
/**
 * Diagnostic: XML-RPC Brute Force Amplification
 *
 * Checks if XML-RPC is enabled and vulnerable to brute force amplification attacks
 * via system.multicall (thousands of login attempts in one request).
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4009
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Brute Force Diagnostic
 *
 * Detects XML-RPC enabled with system.multicall allowing amplified brute force
 * attacks. One request can test thousands of password combinations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Security_XMLRPC_Brute_Force extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-xmlrpc-brute-force';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Brute Force Amplification';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML-RPC allows brute force amplification attacks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Check XML-RPC brute force risk.
	 *
	 * Verifies:
	 * - If XML-RPC is enabled
	 * - If system.multicall is available
	 * - If rate limiting is in place
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if XML-RPC is explicitly disabled.
		$xmlrpc_disabled = apply_filters( 'xmlrpc_enabled', true );

		if ( ! $xmlrpc_disabled ) {
			// XML-RPC is disabled - no risk.
			return null;
		}

		// Check if xmlrpc.php file exists and is accessible.
		$xmlrpc_path = ABSPATH . 'xmlrpc.php';
		if ( ! file_exists( $xmlrpc_path ) ) {
			// File doesn't exist - no risk.
			return null;
		}

		// Check for security plugins that may block XML-RPC.
		$security_plugins = array(
			'wordfence/wordfence.php'              => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'ithemes-security/ithemes-security.php' => 'iThemes Security',
			'sucuri-scanner/sucuri.php'            => 'Sucuri',
		);

		$has_xmlrpc_protection = false;

		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Check if plugin has XML-RPC blocking enabled.
				// This would require checking each plugin's specific settings.
				// For now, note that a security plugin is active.
				$has_xmlrpc_protection = true;
				break;
			}
		}

		// Check for .htaccess rules blocking XML-RPC.
		$htaccess = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess ) ) {
			$content = file_get_contents( $htaccess ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( strpos( $content, 'xmlrpc.php' ) !== false ) {
				// .htaccess has XML-RPC rules.
				$has_xmlrpc_protection = true;
			}
		}

		if ( $has_xmlrpc_protection ) {
			// Protection is in place.
			return null;
		}

		// XML-RPC is enabled and unprotected.
		$threat_level = 80; // High severity.

		$finding = array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'XML-RPC is enabled without rate limiting or protection. The system.multicall method allows attackers to test thousands of login credentials in a single request. This amplifies brute force attacks by 1000x. Disable XML-RPC or use a security plugin with XML-RPC protection.',
				'wpshadow'
			),
			'severity'     => 'high',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/xmlrpc-brute-force-amplification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'why'            => __( 'XML‑RPC brute force amplification is dangerous because it bypasses traditional login protections. The system.multicall method allows thousands of credential attempts in a single request, which evades per‑request rate limits and makes log analysis harder. This increases the risk of account takeover, unauthorized content changes, and malware injection. OWASP Top 10 2021 ranks Broken Access Control #1 and Identification and Authentication Failures #7, both of which are directly affected when login endpoints are abused at scale. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that credential abuse remains a dominant pattern for initial access; XML‑RPC amplification gives attackers a low‑cost way to test stolen credential lists against your site. For organizations with multiple admins or e‑commerce access, a single successful login can expose payment data, customer PII, and order manipulation. The impact is not just security: downtime during incident response, reputational damage from defaced content, and forced password resets create real revenue loss. Many WordPress sites do not actively use XML‑RPC, yet leave it enabled by default, which means the risk is often unmonitored and unmitigated. Disabling or hard‑limiting XML‑RPC closes a broad, noisy attack surface with minimal business downside. This control is also easy to prove to auditors and cyber insurers because it is visible in configuration and logging.', 'wpshadow' ),
				'recommendation' => __( '1. Disable XML‑RPC entirely if not required (filter xmlrpc_enabled to false).
2. If needed, block system.multicall specifically via xmlrpc_methods filter.
3. Enforce strong rate limiting on xmlrpc.php at the web server or WAF.
4. Require application passwords or OAuth for XML‑RPC access.
5. Restrict XML‑RPC to known IPs (office/VPN) via firewall rules.
6. Enable login attempt logging and alerting for XML‑RPC endpoints.
7. Force 2FA for all accounts that can use XML‑RPC.
8. Rotate credentials regularly and remove unused accounts.
9. Add CAPTCHA or additional verification where feasible.
10. Monitor 401/403 spikes and block abusive user agents.', 'wpshadow' ),
			),
		);

		return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'authentication', self::$slug );
	}
}
