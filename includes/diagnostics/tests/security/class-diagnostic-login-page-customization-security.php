<?php
/**
 * Login Page Customization Security Diagnostic
 *
 * Checks security implications of login page customization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Customization Security Diagnostic
 *
 * Validates login page customization for security risks.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Login_Page_Customization_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-customization-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Customization Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks security implications of login page customization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$customizations = array();

		// Check for custom login URL
		$login_url = wp_login_url();
		if ( strpos( $login_url, 'wp-login.php' ) === false ) {
			$customizations[] = __( 'Custom login URL is configured', 'wpshadow' );
		}

		// Check for hidden login page plugins
		$hidden_login_plugins = array(
			'hide-my-wp/index.php',
			'wordfence/wordfence.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$has_hidden_login = false;

		foreach ( $hidden_login_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_hidden_login = true;
				$customizations[] = __( 'Hidden/customized login page enabled', 'wpshadow' );
				break;
			}
		}

		// Check for custom login template/theme
		$custom_login_template = apply_filters( 'wpshadow_custom_login_template', false );
		if ( $custom_login_template ) {
			$customizations[] = __( 'Custom login template active', 'wpshadow' );
		}

		// Check for login redirects
		global $wp_filter;
		if ( isset( $wp_filter['login_redirect'] ) || isset( $wp_filter['wp_login_successful'] ) ) {
			$customizations[] = __( 'Login redirect hooks detected', 'wpshadow' );
		}

		// Check for JavaScript modifications on login page
		if ( isset( $wp_filter['login_enqueue_scripts'] ) ) {
			$customizations[] = __( 'JavaScript modifications on login page', 'wpshadow' );
		}

		// Check for login form modifications
		if ( isset( $wp_filter['login_form'] ) || isset( $wp_filter['login_head'] ) ) {
			$customizations[] = __( 'Login form HTML modifications detected', 'wpshadow' );
		}

		// Validate customizations don't break security
		if ( ! empty( $customizations ) ) {
			// Check if hidden login exposes wp-admin
			if ( $has_hidden_login ) {
				$admin_exposed = false;
				if ( wp_remote_head( admin_url() )['response']['code'] !== 403 ) {
					$admin_exposed = true;
				}

				if ( $admin_exposed ) {
					$issues[] = __( 'Hidden login page is configured, but /wp-admin/ is still accessible', 'wpshadow' );
				}
			}

			// Check for JavaScript injection vectors on login page
			if ( isset( $wp_filter['login_enqueue_scripts'] ) ) {
				$issues[] = __( 'Custom JavaScript on login page may introduce XSS vulnerabilities', 'wpshadow' );
			}

			// Check for password field manipulation
			$password_filter_count = 0;
			if ( isset( $wp_filter['login_form'] ) ) {
				$password_filter_count += count( $wp_filter['login_form'] );
			}

			if ( $password_filter_count > 5 ) {
				$issues[] = __( 'Login form heavily modified - may affect password field security', 'wpshadow' );
			}

			// Check if custom login breaks HTTPS
			if ( is_ssl() ) {
				if ( ! $has_hidden_login && isset( $wp_filter['login_url'] ) ) {
					// Check if custom URL strips HTTPS
					$custom_url = apply_filters( 'login_url', wp_login_url(), false );
					if ( strpos( $custom_url, 'https' ) === false ) {
						$issues[] = __( 'Custom login URL does not use HTTPS', 'wpshadow' );
					}
				}
			}

			// Check if customization interferes with remember me
			if ( isset( $wp_filter['login_init'] ) ) {
				$issues[] = __( 'Login page customization may interfere with remember me functionality', 'wpshadow' );
			}

			// Check if custom login breaks wp-json REST API
			if ( isset( $wp_filter['rest_authentication_errors'] ) ) {
				$issues[] = __( 'Custom login configuration may affect REST API authentication', 'wpshadow' );
			}
		}

		// Check for security best practices
		$recommendations = array(
			__( 'If using custom login URL, also hide /wp-admin/ and /wp-login.php', 'wpshadow' ),
			__( 'Ensure custom login page has security nonces to prevent CSRF', 'wpshadow' ),
			__( 'Test login functionality across browsers and devices', 'wpshadow' ),
			__( 'Verify HTTPS is enforced on all login pages', 'wpshadow' ),
		);

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Login page customization has security implications', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-customization-security',
				'details'      => array(
					'issues'            => $issues,
					'customizations'    => $customizations,
					'recommendations'   => $recommendations,
				),
			);
		}

		return null;
	}
}
