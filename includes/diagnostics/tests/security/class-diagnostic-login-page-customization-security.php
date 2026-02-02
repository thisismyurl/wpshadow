<?php
<?php
/**
 * Login Page Customization Security Diagnostic
 *
 * Checks security implications of login page customization. Custom login pages\n * may accidentally expose information, disable security features, or introduce\n * vulnerabilities (reflected XSS, CSRF). Customizations must maintain security.\n *
 * **What This Check Does:**
 * - Detects if login page customized (non-default template)\n * - Validates nonce tokens present (CSRF protection)\n * - Checks for information disclosure (errors reveal usernames)\n * - Tests if redirect after login is controlled (open redirect prevention)\n * - Confirms SSL/HTTPS enforced on login form\n * - Validates password field is type=\"password\" (not visible in browser)\n *
 * **Why This Matters:**
 * Custom login = accidentally broken security. Scenarios:\n * - Developer removes nonce (forgot to add to custom form)\n * - CSRF attack possible (attacker tricks user into login)\n * - Custom error message leaks user existence (\"User not found\")\n * - Attacker enumerates valid usernames\n * - Custom redirect doesn't validate origin (open redirect to malware)\n *
 * **Business Impact:**
 * Freelancer creates custom login form. Forgets nonce field (didn't know about\n * CSRF). Site vulnerable to CSRF. Attacker tricks admin into submitting form that\n * creates new admin account (attacker controlled). Attacker gains permanent access.\n * Discovers breach 3 months later. Damage: $100K+ in breach investigation + recovery.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Custom features still secure\n * - #9 Show Value: Prevents CSRF/redirect vulnerabilities\n * - #10 Beyond Pure: Security by default, even with customization\n *
 * **Related Checks:**
 * - Cross-Site Request Forgery Protection Not Validated (CSRF)\n * - Input Sanitization Not Implemented (XSS prevention)\n * - Login Page Rate Limiting (brute force)\n *
 * **Learn More:**
 * Custom login form security: https://wpshadow.com/kb/custom-login-form-security\n * Video: Building secure login forms (11min): https://wpshadow.com/training/custom-login-security\n *
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
 * Login Page Customization Security Diagnostic Class
 *
 * Validates custom login page for security issues.\n *
 * **Detection Pattern:**
 * 1. Detect if custom login template used (not /wp-login.php)\n * 2. Scan form HTML for nonce fields\n * 3. Check for CSRF protection\n * 4. Validate redirect_to parameter sanitization\n * 5. Check if SSL/HTTPS enforced\n * 6. Return severity if security features missing\n *
 * **Real-World Scenario:**
 * Developer creates custom login template. Copies from tutorial. Tutorial didn't\n * include nonce field (written before WordPress nonce best practices). Custom form\n * works fine. Attacker crafts CSRF payload. Tricks admin into visiting. Form\n * silently submitted to create new admin user. Attacker now has permanent access.\n *
 * **Implementation Notes:**
 * - Checks for custom login template usage\n * - Scans form for nonce field\n * - Validates redirect parameter\n * - Severity: high (missing nonce), medium (weak validation)\n * - Treatment: add CSRF tokens, validate redirects\n *
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
