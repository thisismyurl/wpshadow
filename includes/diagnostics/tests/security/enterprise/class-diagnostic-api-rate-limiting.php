<?php
/**
 * API Rate Limiting Diagnostic
 *
 * Checks if API endpoints are protected with rate limiting.
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
 * API Rate Limiting Diagnostic Class
 *
 * Verifies that REST API and other API endpoints are protected with rate limiting
 * to prevent abuse, DDoS, and resource exhaustion.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Api_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API endpoints are protected with rate limiting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the API rate limiting diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if rate limiting gaps detected, null otherwise.
	 */
	public static function check() {
		$rate_limiting_methods = array();
		$warnings              = array();

		// Check for WordPress REST API rate limiting plugins.
		$rate_limit_plugins = array(
			'wp-rest-api-rate-limit/wp-rest-api-rate-limit.php',
			'rest-api-rate-limit/rest-api-rate-limit.php',
			'jwt-authentication-for-wp-rest-api/jwt-auth.php',
		);

		foreach ( $rate_limit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$rate_limiting_methods['plugin'] = basename( dirname( $plugin ) );
				break;
			}
		}

		// Check for security plugin rate limiting.
		$security_plugins_with_rate_limit = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'better-wp-security/better-wp-security.php', // iThemes Security.
		);

		foreach ( $security_plugins_with_rate_limit as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$rate_limiting_methods['security_plugin'] = basename( dirname( $plugin ) );
				break;
			}
		}

		// Check for server-level rate limiting.
		// CloudFlare rate limiting.
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$rate_limiting_methods['cloudflare'] = __( 'Cloudflare rate limiting available', 'wpshadow' );
		}

		// Check for AWS WAF indicators.
		if ( getenv( 'AWS_WAF_ENABLED' ) || defined( 'AWS_WAF_WEB_ACL' ) ) {
			$rate_limiting_methods['aws_waf'] = __( 'AWS WAF rate limiting', 'wpshadow' );
		}

		// Check for custom rate limiting implementation.
		$has_custom_rate_limit = has_filter( 'rest_pre_dispatch' ) || 
								 has_filter( 'rest_authentication_errors' );
		
		if ( $has_custom_rate_limit ) {
			$rate_limiting_methods['custom'] = __( 'Custom REST API filters detected', 'wpshadow' );
		}

		// Check if REST API is even enabled.
		$rest_enabled = get_option( 'rest_api_enabled', true );
		if ( ! $rest_enabled || has_filter( 'rest_authentication_errors', '__return_false' ) ) {
			$rate_limiting_methods['api_disabled'] = __( 'REST API disabled', 'wpshadow' );
		}

		// Check for transient-based rate limiting.
		global $wpdb;
		$rate_limit_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			WHERE option_name LIKE '%_transient_rate_limit_%' 
			OR option_name LIKE '%_transient_api_throttle_%'"
		);

		if ( $rate_limit_transients > 0 ) {
			$rate_limiting_methods['transient_based'] = sprintf(
				/* translators: %d: number of rate limit transients */
				__( 'Transient-based rate limiting (%d active limits)', 'wpshadow' ),
				$rate_limit_transients
			);
		}

		// If no rate limiting is detected.
		if ( empty( $rate_limiting_methods ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site\'s API (the system that lets apps and services talk to your site) could benefit from request limiting. Think of it like a velvet rope at a club—it controls how many people can enter at once. This prevents anyone from overwhelming your server with too many requests.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-rate-limiting',
				'context'      => array(
					'rest_enabled' => $rest_enabled,
				),
			);
		}

		// If only REST API disabled (not real rate limiting).
		if ( count( $rate_limiting_methods ) === 1 && isset( $rate_limiting_methods['api_disabled'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site\'s API (the connection that lets apps talk to WordPress) is currently turned off. While this protects it, many modern plugins and features need the API to work. Consider enabling it with request limits for better functionality and security.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-rate-limiting',
				'context'      => array(
					'rate_limiting_methods' => $rate_limiting_methods,
				),
			);
		}

		// Check for best practices.
		if ( isset( $rate_limiting_methods['transient_based'] ) ) {
			$warnings[] = __( 'Transient-based rate limiting may not be reliable in multi-server environments', 'wpshadow' );
		}

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'API rate limiting is configured but has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-rate-limiting',
				'context'      => array(
					'rate_limiting_methods' => $rate_limiting_methods,
					'warnings'              => $warnings,
				),
			);
		}

		return null; // Rate limiting is properly configured.
	}
}
