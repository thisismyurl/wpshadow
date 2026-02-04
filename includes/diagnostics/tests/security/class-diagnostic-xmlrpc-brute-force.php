<?php
/**
 * XML-RPC Brute Force Amplification Diagnostic
 *
 * Checks if XML-RPC is enabled and vulnerable to brute force amplification
 * attacks via the system.multicall method.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.1540
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Brute Force Amplification Diagnostic Class
 *
 * Detects XML-RPC vulnerabilities that allow brute force amplification
 * attacks and provides recommendations for mitigation.
 *
 * @since 1.6035.1540
 */
class Diagnostic_XMLRPC_Brute_Force extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'prevents_xmlrpc_brute_force';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Brute Force Amplification';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies XML-RPC is secured against brute force amplification attacks';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1540
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check if XML-RPC is disabled (40 points).
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );

		if ( ! $xmlrpc_enabled ) {
			$earned_points           += 40;
			$stats['xmlrpc_disabled'] = true;
		} else {
			$issues[] = 'XML-RPC is enabled and potentially vulnerable';
			$stats['xmlrpc_enabled'] = true;

			// Check for system.multicall method (additional vulnerability).
			if ( method_exists( 'wp_xmlrpc_server', 'multiCall' ) ) {
				$issues[] = 'XML-RPC system.multicall method available (allows 1000s of login attempts in one request)';
				$stats['multicall_available'] = true;
			}
		}

		// Check for security plugins that disable XML-RPC (30 points).
		$security_plugins = array(
			'disable-xml-rpc/disable-xml-rpc.php'           => 'Disable XML-RPC',
			'disable-xml-rpc-api/disable-xml-rpc-api.php'   => 'Disable XML-RPC-API',
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
		);

		$active_protection = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_protection[] = $plugin_name;
				$earned_points      += 10; // Up to 30 points.

				// Check iThemes Security specific XML-RPC settings.
				if ( 'better-wp-security/better-wp-security.php' === $plugin_file ) {
					$ithemes_settings = get_site_option( 'itsec_global', array() );
					if ( ! empty( $ithemes_settings['disable_xmlrpc'] ) ) {
						$stats['ithemes_xmlrpc_disabled'] = true;
					}
				}

				// Check Wordfence XML-RPC settings.
				if ( 'wordfence/wordfence.php' === $plugin_file ) {
					$wf_config = get_option( 'wordfenceActivated', false );
					if ( $wf_config ) {
						$stats['wordfence_active'] = true;
					}
				}
			}
		}

		if ( count( $active_protection ) > 0 ) {
			$stats['xmlrpc_protection_plugins'] = implode( ', ', $active_protection );
		} else {
			$issues[] = 'No XML-RPC protection plugins detected';
		}

		// Check for rate limiting plugins (20 points).
		$rate_limit_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'loginizer/loginizer.php'                                         => 'Loginizer',
			'wp-limit-login-attempts/wp-limit-login-attempts.php'             => 'WP Limit Login Attempts',
			'wp-fail2ban/wp-fail2ban.php'                                     => 'WP fail2ban',
		);

		$active_rate_limit = array();
		foreach ( $rate_limit_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_rate_limit[] = $plugin_name;
				$earned_points      += 7; // Up to 20 points.
			}
		}

		if ( count( $active_rate_limit ) > 0 ) {
			$stats['rate_limit_plugins'] = implode( ', ', $active_rate_limit );
		} else {
			$warnings[] = 'No rate limiting plugins detected';
		}

		// Check for web application firewall (10 points).
		$waf_plugins = array(
			'ninjafirewall/ninjafirewall.php'       => 'NinjaFirewall',
			'wordfence/wordfence.php'               => 'Wordfence WAF',
			'sucuri-scanner/sucuri.php'             => 'Sucuri WAF',
		);

		$active_waf = array();
		foreach ( $waf_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_waf[]   = $plugin_name;
				$earned_points += 5; // Up to 10 points.
			}
		}

		if ( count( $active_waf ) > 0 ) {
			$stats['waf_protection'] = implode( ', ', $active_waf );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 60% (critical for brute force).
		if ( $score < 60 ) {
			$severity     = $score < 40 ? 'high' : 'medium';
			$threat_level = $score < 40 ? 85 : 70;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your XML-RPC security scored %s. XML-RPC can be exploited for brute force amplification attacks where attackers try thousands of password combinations in a single request using system.multicall. This can bypass rate limiting and quickly compromise accounts.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/xmlrpc-brute-force-amplification',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
