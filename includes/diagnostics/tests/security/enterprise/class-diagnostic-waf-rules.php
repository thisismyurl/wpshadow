<?php
/**
 * WAF Rules Diagnostic
 *
 * Checks if a web application firewall with proper rules is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WAF Rules Diagnostic Class
 *
 * Verifies that a web application firewall (WAF) is configured with
 * appropriate rules to protect against common web attacks.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Waf_Rules extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'waf-rules';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Web Application Firewall (WAF) Rules';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a web application firewall with proper rules is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the WAF rules diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if WAF gaps detected, null otherwise.
	 */
	public static function check() {
		$waf_detected = array();
		$warnings     = array();

		// Check for cloud-based WAF services.
		// Cloudflare WAF.
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$waf_detected['cloudflare'] = 'Cloudflare WAF';
			
			// Check if WAF is enabled (not just CDN).
			if ( ! isset( $_SERVER['HTTP_CF_FIREWALL_RULES'] ) ) {
				$warnings[] = __( 'Cloudflare WAF may not be fully enabled', 'wpshadow' );
			}
		}

		// AWS WAF.
		if ( isset( $_SERVER['HTTP_X_AMZN_WAFREGIONALTOKEN'] ) || 
			 defined( 'AWS_WAF_WEB_ACL' ) || 
			 getenv( 'AWS_WAF_ENABLED' ) ) {
			$waf_detected['aws_waf'] = 'AWS WAF';
		}

		// Akamai WAF.
		if ( isset( $_SERVER['HTTP_X_AKAMAI_TRANSFORMED'] ) ) {
			$waf_detected['akamai'] = 'Akamai WAF';
		}

		// Imperva/Incapsula.
		if ( isset( $_SERVER['HTTP_X_IINFO'] ) || isset( $_SERVER['HTTP_X_CDN'] ) ) {
			if ( strpos( $_SERVER['HTTP_X_CDN'] ?? '', 'Incapsula' ) !== false ) {
				$waf_detected['imperva'] = 'Imperva/Incapsula WAF';
			}
		}

		// Check for server-level WAF.
		// ModSecurity (Apache/Nginx).
		if ( isset( $_SERVER['HTTP_X_WAF'] ) || 
			 file_exists( '/etc/modsecurity/modsecurity.conf' ) ||
			 file_exists( '/etc/apache2/mods-enabled/security2.conf' ) ) {
			$waf_detected['modsecurity'] = 'ModSecurity';
		}

		// Check for WordPress WAF plugins.
		$waf_plugins = array(
			'wordfence/wordfence.php'                              => 'Wordfence WAF',
			'ninjafirewall/ninjafirewall.php'                      => 'NinjaFirewall',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security WAF',
			'sucuri-scanner/sucuri.php'                            => 'Sucuri WAF',
		);

		foreach ( $waf_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$waf_detected[ sanitize_key( $name ) ] = $name;
			}
		}

		// If no WAF detected.
		if ( empty( $waf_detected ) ) {
			$is_enterprise = self::is_enterprise_environment();
			
			$severity     = $is_enterprise ? 'high' : 'medium';
			$threat_level = $is_enterprise ? 80 : 65;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Adding a web application firewall (think of it as a security guard for your website) helps protect against common attacks. It checks incoming requests and blocks suspicious ones before they reach your site—like a spam filter for website visitors.', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/waf-rules',
				'context'      => array(
					'is_enterprise' => $is_enterprise,
				),
			);
		}

		// WAF is detected - check configuration quality.
		// Check if rules are in blocking mode or just logging.
		if ( isset( $waf_detected['cloudflare'] ) ) {
			// Check Cloudflare security level.
			if ( ! isset( $_SERVER['HTTP_CF_FIREWALL_MODE'] ) || 
				 $_SERVER['HTTP_CF_FIREWALL_MODE'] === 'simulate' ) {
				$warnings[] = __( 'Cloudflare WAF may be in simulation mode, not blocking', 'wpshadow' );
			}
		}

		// Check if using plugin-based WAF (less effective than cloud/server WAF).
		$plugin_based_only = true;
		foreach ( array( 'cloudflare', 'aws_waf', 'akamai', 'imperva', 'modsecurity' ) as $enterprise_waf ) {
			if ( isset( $waf_detected[ $enterprise_waf ] ) ) {
				$plugin_based_only = false;
				break;
			}
		}

		if ( $plugin_based_only && self::is_enterprise_environment() ) {
			$warnings[] = __( 'Plugin-based WAF only - consider cloud or server-level WAF for better protection', 'wpshadow' );
		}

		// Check for OWASP Core Rule Set (CRS) if using ModSecurity.
		if ( isset( $waf_detected['modsecurity'] ) ) {
			$crs_path = '/usr/share/modsecurity-crs/';
			if ( ! file_exists( $crs_path ) && ! file_exists( '/etc/modsecurity/crs/' ) ) {
				$warnings[] = __( 'ModSecurity detected but OWASP CRS rules may not be installed', 'wpshadow' );
			}
		}

		// If WAF detected but has warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: WAF name(s), 2: list of warnings */
					__( 'WAF detected (%1$s) but has recommendations: %2$s', 'wpshadow' ),
					implode( ', ', $waf_detected ),
					implode( '; ', $warnings )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/waf-rules',
				'context'      => array(
					'waf_detected' => $waf_detected,
					'warnings'     => $warnings,
				),
			);
		}

		return null; // WAF is properly configured.
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since 1.6093.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			is_multisite() && get_blog_count() > 50,
		);

		return in_array( true, $enterprise_indicators, true );
	}
}
