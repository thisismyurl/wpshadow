<?php
/**
 * VPN/Secure Access Diagnostic
 *
 * Checks if secure remote access is configured for admin areas.
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
 * VPN/Secure Access Diagnostic Class
 *
 * Verifies that secure remote access (VPN, IP allowlisting) is configured
 * for administrative areas in enterprise environments.
 *
 * @since 1.6035.1200
 */
class Diagnostic_Vpn_Secure_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'vpn-secure-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'VPN/Secure Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if secure remote access is configured for admin areas';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the VPN/secure access diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if secure access gaps detected, null otherwise.
	 */
	public static function check() {
		$access_controls = array();
		$warnings        = array();

		// Check for IP restriction plugins.
		$ip_restriction_plugins = array(
			'wp-cerber/wp-cerber.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'wordfence/wordfence.php',
		);

		foreach ( $ip_restriction_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$access_controls['security_plugin'] = basename( dirname( $plugin ) );
				break;
			}
		}

		// Check for IP allowlist constants.
		if ( defined( 'WP_ADMIN_ALLOWED_IPS' ) || defined( 'ADMIN_IP_WHITELIST' ) ) {
			$access_controls['ip_allowlist'] = __( 'IP allowlist configured', 'wpshadow' );
		}

		// Check for .htaccess IP restrictions.
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) && is_readable( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( strpos( $htaccess_content, 'Allow from' ) !== false || 
				 strpos( $htaccess_content, 'Require ip' ) !== false ) {
				$access_controls['htaccess'] = __( '.htaccess IP restrictions', 'wpshadow' );
			}
		}

		// Check for VPN indicators.
		// Check if request is coming from common VPN IP ranges.
		$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if ( self::is_vpn_ip( $client_ip ) ) {
			$access_controls['vpn_detected'] = __( 'VPN connection detected', 'wpshadow' );
		}

		// Check for VPN-only access configurations.
		if ( defined( 'VPN_REQUIRED' ) && VPN_REQUIRED ) {
			$access_controls['vpn_required'] = __( 'VPN requirement enforced', 'wpshadow' );
		}

		// Check for bastion host / jump server indicators.
		if ( isset( $_SERVER['HTTP_X_BASTION_HOST'] ) || getenv( 'BASTION_HOST' ) ) {
			$access_controls['bastion'] = __( 'Bastion host access configured', 'wpshadow' );
		}

		// Check for zero-trust network indicators.
		if ( isset( $_SERVER['HTTP_X_ZTNA_TOKEN'] ) || defined( 'ZTNA_ENABLED' ) ) {
			$access_controls['zero_trust'] = __( 'Zero-trust network access configured', 'wpshadow' );
		}

		// Check for Cloudflare Access or similar.
		if ( isset( $_SERVER['HTTP_CF_ACCESS_AUTHENTICATED_USER_EMAIL'] ) ) {
			$access_controls['cloudflare_access'] = __( 'Cloudflare Access protecting admin', 'wpshadow' );
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and no access controls.
		if ( $is_enterprise && empty( $access_controls ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No secure remote access controls detected in enterprise environment. Consider VPN requirements, IP allowlisting, or zero-trust access for admin areas.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/vpn-secure-access',
				'context'      => array(
					'is_enterprise' => $is_enterprise,
					'client_ip'     => $client_ip,
				),
			);
		}

		// If access controls exist, check for best practices.
		if ( ! empty( $access_controls ) ) {
			// Check if 2FA is also enabled.
			$two_factor_plugins = array(
				'two-factor/two-factor.php',
				'two-factor-authentication/two-factor-authentication.php',
				'wordfence/wordfence.php',
			);

			$has_2fa = false;
			foreach ( $two_factor_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_2fa = true;
					break;
				}
			}

			if ( ! $has_2fa ) {
				$warnings[] = __( 'Two-factor authentication recommended alongside access controls', 'wpshadow' );
			}

			// Check if access is only IP-based (not as secure as VPN/ZTNA).
			if ( isset( $access_controls['ip_allowlist'] ) && 
				 ! isset( $access_controls['vpn_required'] ) && 
				 ! isset( $access_controls['zero_trust'] ) ) {
				$warnings[] = __( 'IP allowlisting alone is less secure than VPN or zero-trust access', 'wpshadow' );
			}

			if ( ! empty( $warnings ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Secure access controls are configured but have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/vpn-secure-access',
					'context'      => array(
						'access_controls' => $access_controls,
						'warnings'        => $warnings,
					),
				);
			}
		}

		return null; // Secure access is properly configured or not needed.
	}

	/**
	 * Check if IP appears to be from a VPN.
	 *
	 * @since  1.6035.1200
	 * @param  string $ip IP address to check.
	 * @return bool True if IP appears to be from VPN, false otherwise.
	 */
	private static function is_vpn_ip( $ip ) {
		// Common VPN/private network ranges.
		$vpn_ranges = array(
			'10.0.0.0/8',     // Private network.
			'172.16.0.0/12',  // Private network.
			'192.168.0.0/16', // Private network.
		);

		foreach ( $vpn_ranges as $range ) {
			if ( self::ip_in_range( $ip, $range ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if IP is in CIDR range.
	 *
	 * @since  1.6035.1200
	 * @param  string $ip    IP address to check.
	 * @param  string $range CIDR range (e.g., '192.168.0.0/16').
	 * @return bool True if IP is in range, false otherwise.
	 */
	private static function ip_in_range( $ip, $range ) {
		if ( strpos( $range, '/' ) === false ) {
			$range .= '/32';
		}

		list( $range_ip, $netmask ) = explode( '/', $range, 2 );
		$range_decimal              = ip2long( $range_ip );
		$ip_decimal                 = ip2long( $ip );
		$wildcard_decimal           = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal            = ~$wildcard_decimal;

		return ( ( $ip_decimal & $netmask_decimal ) === ( $range_decimal & $netmask_decimal ) );
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
