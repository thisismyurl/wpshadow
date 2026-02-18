<?php
/**
 * DDoS Mitigation Diagnostic
 *
 * Checks if DDoS protection service is active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DDoS Mitigation Diagnostic Class
 *
 * Verifies that DDoS protection is configured to protect against
 * distributed denial of service attacks.
 *
 * @since 1.6035.1200
 */
class Diagnostic_Ddos_Mitigation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ddos-mitigation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DDoS Mitigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DDoS protection service is active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the DDoS mitigation diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if DDoS protection gaps detected, null otherwise.
	 */
	public static function check() {
		$ddos_protection = array();
		$warnings        = array();

		// Check for Cloudflare (includes DDoS protection).
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$ddos_protection['cloudflare'] = 'Cloudflare DDoS Protection';
			
			// Check if under attack mode or I'm Under Attack Mode.
			if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
				// Cloudflare is actively processing traffic.
				$ddos_protection['cloudflare_active'] = __( 'Cloudflare actively filtering traffic', 'wpshadow' );
			}
		}

		// Check for AWS Shield.
		if ( defined( 'AWS_SHIELD_ENABLED' ) || 
			 getenv( 'AWS_SHIELD_ADVANCED' ) ||
			 isset( $_SERVER['HTTP_X_AMZN_TRACE_ID'] ) ) {
			$ddos_protection['aws_shield'] = 'AWS Shield';
			
			if ( getenv( 'AWS_SHIELD_ADVANCED' ) ) {
				$ddos_protection['aws_shield_advanced'] = __( 'AWS Shield Advanced active', 'wpshadow' );
			}
		}

		// Check for Akamai DDoS protection.
		if ( isset( $_SERVER['HTTP_X_AKAMAI_TRANSFORMED'] ) || 
			 defined( 'AKAMAI_EDGE_HOSTNAME' ) ) {
			$ddos_protection['akamai'] = 'Akamai DDoS Protection';
		}

		// Check for Imperva/Incapsula.
		if ( isset( $_SERVER['HTTP_X_IINFO'] ) ) {
			$ddos_protection['imperva'] = 'Imperva DDoS Protection';
		}

		// Check for Google Cloud Armor.
		if ( isset( $_SERVER['HTTP_X_CLOUD_TRACE_CONTEXT'] ) && 
			 ( defined( 'GOOGLE_CLOUD_ARMOR' ) || getenv( 'GCP_CLOUD_ARMOR' ) ) ) {
			$ddos_protection['cloud_armor'] = 'Google Cloud Armor';
		}

		// Check for Azure DDoS Protection.
		if ( defined( 'AZURE_DDOS_PROTECTION' ) || getenv( 'AZURE_DDOS_ENABLED' ) ) {
			$ddos_protection['azure_ddos'] = 'Azure DDoS Protection';
		}

		// Check for rate limiting (basic DDoS mitigation).
		$has_rate_limiting = false;
		$rate_limit_plugins = array(
			'wordfence/wordfence.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'wp-fail2ban/wp-fail2ban.php',
		);

		foreach ( $rate_limit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rate_limiting = true;
				$ddos_protection['rate_limiting'] = basename( dirname( $plugin ) );
				break;
			}
		}

		// Check for server-level rate limiting.
		// Nginx rate limiting.
		if ( isset( $_SERVER['HTTP_X_RATE_LIMIT'] ) || 
			 file_exists( '/etc/nginx/conf.d/rate-limit.conf' ) ) {
			$ddos_protection['nginx_rate_limit'] = __( 'Nginx rate limiting configured', 'wpshadow' );
			$has_rate_limiting = true;
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If no DDoS protection at all.
		if ( empty( $ddos_protection ) ) {
			$severity     = $is_enterprise ? 'high' : 'medium';
			$threat_level = $is_enterprise ? 85 : 70;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No DDoS protection detected. Site is vulnerable to distributed denial of service attacks that can cause downtime.', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ddos-mitigation',
				'context'      => array(
					'is_enterprise' => $is_enterprise,
				),
			);
		}

		// If only rate limiting (weak DDoS protection).
		if ( count( $ddos_protection ) === 1 && $has_rate_limiting ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Only basic rate limiting detected. Consider dedicated DDoS protection service (Cloudflare, AWS Shield, etc.) for better protection.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ddos-mitigation',
				'context'      => array(
					'ddos_protection' => $ddos_protection,
				),
			);
		}

		// Check for best practices.
		// Check if both CDN-level and rate limiting are configured (defense in depth).
		$has_cdn_protection = isset( $ddos_protection['cloudflare'] ) || 
							  isset( $ddos_protection['akamai'] ) ||
							  isset( $ddos_protection['imperva'] );

		if ( $has_cdn_protection && ! $has_rate_limiting ) {
			$warnings[] = __( 'Consider adding server-level rate limiting as additional layer', 'wpshadow' );
		}

		// Check if using free tier Cloudflare (limited DDoS protection).
		if ( isset( $ddos_protection['cloudflare'] ) && 
			 ! isset( $ddos_protection['cloudflare_active'] ) ) {
			$warnings[] = __( 'Cloudflare free tier has limited DDoS protection - consider Pro or Business plan', 'wpshadow' );
		}

		// Check if caching is enabled (helps mitigate some DDoS).
		if ( ! wp_using_ext_object_cache() && ! defined( 'WP_CACHE' ) ) {
			$warnings[] = __( 'Enable caching to improve DDoS resilience', 'wpshadow' );
		}

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: DDoS protection service(s), 2: list of warnings */
					__( 'DDoS protection active (%1$s) but has recommendations: %2$s', 'wpshadow' ),
					implode( ', ', $ddos_protection ),
					implode( '; ', $warnings )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ddos-mitigation',
				'context'      => array(
					'ddos_protection' => $ddos_protection,
					'warnings'        => $warnings,
				),
			);
		}

		return null; // DDoS protection is properly configured.
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since  1.6035.1200
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
