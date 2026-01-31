<?php
/**
 * Login Attempt Rate Limiting Diagnostic
 *
 * Checks if WordPress has login attempt rate limiting configured to prevent brute force attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Attempt Rate Limiting Diagnostic Class
 *
 * Detects if login attempt rate limiting is configured.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Login_Attempt_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-attempt-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Attempt Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login attempt rate limiting is configured to prevent brute force attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if any rate limiting plugin is active
		$rate_limiting_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'loginizer/loginizer.php',
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'wps-limit-login/wps-limit-login.php',
		);

		$rate_limiter_active = false;
		$rate_limiting_method = '';

		foreach ( $rate_limiting_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$rate_limiter_active = true;
				$rate_limiting_method = str_replace( '/wordfence.php', '', str_replace( '/', ' ', $plugin ) );
				break;
			}
		}

		// Check for .htaccess rate limiting rules
		if ( ! $rate_limiter_active ) {
			$htaccess_path = ABSPATH . '.htaccess';
			if ( file_exists( $htaccess_path ) ) {
				$htaccess_content = file_get_contents( $htaccess_path );
				if ( strpos( $htaccess_content, 'limit-noreq' ) !== false ||
					 strpos( $htaccess_content, 'ratelimit' ) !== false ||
					 strpos( $htaccess_content, 'limitrequestrate' ) !== false ) {
					$rate_limiter_active = true;
					$rate_limiting_method = '.htaccess rules';
				}
			}
		}

		// Check for WordPress configuration constants
		if ( ! $rate_limiter_active ) {
			if ( defined( 'WP_LOGIN_RATE_LIMIT' ) || defined( 'WP_LOGIN_ATTEMPT_LIMIT' ) ) {
				$rate_limiter_active = true;
				$rate_limiting_method = 'WordPress constants';
			}
		}

		if ( ! $rate_limiter_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No login rate limiting detected. Brute force attacks could compromise your site security.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/login-attempt-rate-limiting',
			);
		}

		return null;
	}
}
