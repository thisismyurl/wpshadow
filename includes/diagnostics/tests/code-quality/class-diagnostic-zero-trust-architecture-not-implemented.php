<?php
/**
 * Zero Trust Architecture Not Implemented Diagnostic
 *
 * Checks zero trust.
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
 * Diagnostic_Zero_Trust_Architecture_Not_Implemented Class
 *
 * Performs diagnostic check for Zero Trust Architecture Not Implemented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Zero_Trust_Architecture_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'zero-trust-architecture-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Zero Trust Architecture Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks zero trust';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$signals = array(
			'two_factor'       => self::has_two_factor(),
			'login_protection' => self::has_login_protection(),
			'security_plugin'  => self::has_security_plugin(),
			'rest_auth_filter' => has_filter( 'rest_authentication_errors' ),
			'https_enforced'   => self::is_https_enforced(),
		);

		$enabled_signals = array_filter( $signals );
		$score           = count( $enabled_signals );

		if ( $score >= 3 ) {
			return null;
		}

		$missing = array();
		foreach ( $signals as $signal => $enabled ) {
			if ( ! $enabled ) {
				$missing[] = $signal;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Zero trust is a security approach that checks every request like a passport control, even if the request comes from inside your network. We could not find enough signals that this approach is in place. Adding a few common protections can make it much harder for attackers to move around if one account is compromised.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/zero-trust-architecture-not-implemented',
			'details'      => array(
				'enabled_signals' => array_keys( $enabled_signals ),
				'missing_signals' => $missing,
				'recommendation'  => __( 'Start with two-factor login protection, add rate limiting for login attempts, and ensure your site uses HTTPS everywhere. These are practical steps that follow the zero trust idea without complex setup.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Check for two-factor authentication plugins.
	 *
	 * @since 1.6093.1200
	 * @return bool True if a 2FA plugin is active.
	 */
	private static function has_two_factor() {
		$two_factor_plugins = array(
			'two-factor/two-factor.php' => 'Two-Factor',
			'wordfence/wordfence.php'   => 'Wordfence',
			'google-authenticator/google-authenticator.php' => 'Google Authenticator',
			'wp-2fa/wp-2fa.php'         => 'WP 2FA',
			'miniorange-2-factor-authentication/miniorange_2_factor_settings.php' => 'miniOrange 2FA',
		);

		foreach ( $two_factor_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for login rate limiting or lockout protections.
	 *
	 * @since 1.6093.1200
	 * @return bool True if login protection is active.
	 */
	private static function has_login_protection() {
		$protection_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'wordfence/wordfence.php'               => 'Wordfence',
			'ithemes-security/ithemes-security.php' => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
		);

		foreach ( $protection_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for a general security plugin or firewall.
	 *
	 * @since 1.6093.1200
	 * @return bool True if a security plugin is active.
	 */
	private static function has_security_plugin() {
		$security_plugins = array(
			'wordfence/wordfence.php'               => 'Wordfence',
			'sucuri-scanner/sucuri.php'             => 'Sucuri',
			'ithemes-security/ithemes-security.php' => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
		);

		foreach ( $security_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if HTTPS is enforced for home URL.
	 *
	 * @since 1.6093.1200
	 * @return bool True if home URL uses HTTPS.
	 */
	private static function is_https_enforced() {
		$home_url = wp_parse_url( home_url() );
		return isset( $home_url['scheme'] ) && 'https' === $home_url['scheme'];
	}
}
