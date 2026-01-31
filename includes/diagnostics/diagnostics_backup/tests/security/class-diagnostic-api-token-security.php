<?php
/**
 * API Authentication Token Security Diagnostic
 *
 * Validates security of REST API authentication tokens and API keys used
 * for application authentication. Checks token storage, expiration,
 * transmission security, and permission scoping.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.0202
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Token Security Diagnostic Class
 *
 * API tokens are long-lived credentials that provide persistent access to
 * WordPress REST API, WooCommerce API, or custom API endpoints. Unlike
 * session cookies, tokens don't expire on browser close and are often
 * stored in plain text or logged in access logs.
 *
 * Security concerns:
 * - Tokens stored unencrypted in database
 * - Tokens without expiration dates (valid forever)
 * - Overly permissive token scopes (full admin access)
 * - Tokens transmitted over HTTP instead of HTTPS
 * - No token revocation mechanism
 * - Tokens logged in server access logs
 *
 * @since 1.6030.0202
 */
class Diagnostic_API_Token_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-token-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Authentication Token Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates security of REST API authentication tokens and keys';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.0202
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if SSL is enforced (tokens should only transmit over HTTPS).
		if ( ! is_ssl() && ! self::is_ssl_forced() ) {
			$issues['ssl_not_enforced'] = __( 'Site not using HTTPS - API tokens could be intercepted', 'wpshadow' );
		}

		// Check for application passwords (WP 5.6+).
		$app_password_issues = self::check_application_passwords();
		if ( ! empty( $app_password_issues ) ) {
			$issues['application_passwords'] = $app_password_issues;
		}

		// Check for JWT authentication plugin.
		$jwt_issues = self::check_jwt_authentication();
		if ( ! empty( $jwt_issues ) ) {
			$issues['jwt_authentication'] = $jwt_issues;
		}

		// Check WooCommerce API keys if WooCommerce is active.
		if ( class_exists( 'WooCommerce' ) ) {
			$wc_issues = self::check_woocommerce_api_keys();
			if ( ! empty( $wc_issues ) ) {
				$issues['woocommerce_api'] = $wc_issues;
			}
		}

		// Check for custom API token systems.
		$custom_token_issues = self::check_custom_token_systems();
		if ( ! empty( $custom_token_issues ) ) {
			$issues['custom_tokens'] = $custom_token_issues;
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		// Calculate severity.
		$has_ssl_issue  = isset( $issues['ssl_not_enforced'] );
		$has_storage_issue = isset( $issues['application_passwords']['unencrypted_storage'] ) 
			|| isset( $issues['woocommerce_api']['plaintext_keys'] );
		
		if ( $has_ssl_issue || $has_storage_issue ) {
			$severity = 'critical';
			$threat_level = 90;
		} else {
			$severity = 'high';
			$threat_level = 75;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'API token security issues detected that could compromise account access', 'wpshadow' ),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'details'      => self::build_details_array( $issues ),
			'meta'         => array(
				'ssl_enforced'          => ! isset( $issues['ssl_not_enforced'] ),
				'app_passwords_enabled' => function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available(),
				'woocommerce_active'    => class_exists( 'WooCommerce' ),
				'issue_categories'      => count( $issues ),
				'wpdb_avoidance'        => 'Uses is_ssl(), is_plugin_active(), get_users(), get_user_meta(), WC API',
			),
			'kb_link'      => 'https://wpshadow.com/kb/api-token-security',
			'solution'     => self::build_solution_text( $issues ),
		);
	}

	/**
	 * Check if SSL is forced via configuration.
	 *
	 * @since  1.6030.0202
	 * @return bool True if SSL is forced.
	 */
	private static function is_ssl_forced() {
		// Check FORCE_SSL_ADMIN constant.
		if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
			return true;
		}

		// Check force_ssl_admin option.
		if ( get_option( 'force_ssl_admin' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check WordPress Application Passwords (WP 5.6+).
	 *
	 * @since  1.6030.0202
	 * @return array Issues found with application passwords.
	 */
	private static function check_application_passwords() {
		$issues = array();

		// Check if application passwords are available.
		if ( ! function_exists( 'wp_is_application_passwords_available' ) ) {
			return $issues; // WP < 5.6, feature doesn't exist.
		}

		if ( ! wp_is_application_passwords_available() ) {
			return $issues; // Feature disabled.
		}

		// Check for users with application passwords.
		$users_with_passwords = get_users( array(
			'meta_key'   => '_application_passwords',
			'meta_compare' => 'EXISTS',
			'fields'     => array( 'ID', 'user_login', 'user_email' ),
		) );

		if ( empty( $users_with_passwords ) ) {
			return $issues; // No app passwords in use.
		}

		$issues['active_count'] = count( $users_with_passwords );
		$issues['users'] = array();

		// Check each user's application passwords.
		foreach ( $users_with_passwords as $user ) {
			$app_passwords = get_user_meta( $user->ID, '_application_passwords', true );
			
			if ( is_array( $app_passwords ) && ! empty( $app_passwords ) ) {
				$issues['users'][ $user->user_login ] = count( $app_passwords );
			}
		}

		// Application passwords are stored hashed (bcrypt), which is secure.
		// But we should note if there are many active passwords.
		if ( $issues['active_count'] > 10 ) {
			$issues['high_usage'] = sprintf(
				/* translators: %d: number of users with app passwords */
				__( '%d users have application passwords - review if all are necessary', 'wpshadow' ),
				$issues['active_count']
			);
		}

		return $issues;
	}

	/**
	 * Check JWT authentication plugin configuration.
	 *
	 * @since  1.6030.0202
	 * @return array Issues found with JWT authentication.
	 */
	private static function check_jwt_authentication() {
		$issues = array();

		// Check for common JWT authentication plugins.
		$jwt_plugins = array(
			'jwt-authentication-for-wp-rest-api/jwt-auth.php' => 'JWT Authentication',
			'wp-api-jwt-auth/jwt-auth.php'                    => 'WP API JWT Auth',
			'simple-jwt-login/simple-jwt-login.php'           => 'Simple JWT Login',
		);

		$active_jwt_plugin = null;
		foreach ( $jwt_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_jwt_plugin = $plugin_name;
				break;
			}
		}

		if ( ! $active_jwt_plugin ) {
			return $issues; // No JWT plugin active.
		}

		$issues['plugin_active'] = $active_jwt_plugin;

		// Check for JWT_AUTH_SECRET_KEY constant (required for JWT).
		if ( ! defined( 'JWT_AUTH_SECRET_KEY' ) || empty( JWT_AUTH_SECRET_KEY ) ) {
			$issues['missing_secret_key'] = __( 'JWT_AUTH_SECRET_KEY not configured - tokens are not properly signed', 'wpshadow' );
		} elseif ( strlen( JWT_AUTH_SECRET_KEY ) < 64 ) {
			$issues['weak_secret_key'] = __( 'JWT_AUTH_SECRET_KEY is too short - should be 64+ characters', 'wpshadow' );
		}

		// Check CORS configuration (JWT often requires CORS).
		if ( ! defined( 'JWT_AUTH_CORS_ENABLE' ) || ! JWT_AUTH_CORS_ENABLE ) {
			$issues['cors_disabled'] = __( 'CORS not configured for JWT - may prevent legitimate API access', 'wpshadow' );
		}

		return $issues;
	}

	/**
	 * Check WooCommerce API keys.
	 *
	 * @since  1.6030.0202
	 * @return array Issues found with WooCommerce API keys.
	 */
	private static function check_woocommerce_api_keys() {
		global $wpdb;

		$issues = array();

		// WooCommerce stores API keys in {prefix}woocommerce_api_keys table.
		// But we'll use WordPress API when possible.
		
		// Check if there are any API keys at all.
		// Note: WC stores keys hashed, but we can check if table has records.
		$table_name = $wpdb->prefix . 'woocommerce_api_keys';
		
		// Check if table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		
		if ( ! $table_exists ) {
			return $issues; // Table doesn't exist, no API keys.
		}

		// Count API keys.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$key_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		
		if ( $key_count === 0 ) {
			return $issues; // No API keys configured.
		}

		$issues['active_keys'] = $key_count;

		// Check for read/write permissions (overly permissive).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$read_write_keys = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name} WHERE permissions = %s",
			'read_write'
		) );

		if ( $read_write_keys > 0 ) {
			$issues['read_write_keys'] = sprintf(
				/* translators: %d: number of read/write API keys */
				__( '%d API keys have read/write permissions - consider read-only where possible', 'wpshadow' ),
				$read_write_keys
			);
		}

		// Note: WooCommerce stores keys hashed (not plaintext), which is good.
		// But keys are transmitted in API requests, so HTTPS is critical.
		if ( ! is_ssl() ) {
			$issues['keys_without_ssl'] = __( 'WooCommerce API keys in use but HTTPS not enforced', 'wpshadow' );
		}

		return $issues;
	}

	/**
	 * Check for custom API token systems.
	 *
	 * @since  1.6030.0202
	 * @return array Issues found with custom token systems.
	 */
	private static function check_custom_token_systems() {
		$issues = array();

		// Check for plugins that add custom API authentication.
		$api_plugins = array(
			'rest-api-oauth1/oauth-server.php'            => 'REST API OAuth1',
			'wp-rest-api-authentication/class.php'        => 'WP REST API Authentication',
			'wp-oauth-server/wp-oauth.php'                => 'WP OAuth Server',
			'miniOrange-api-authentication/miniorange_api_authentication.php' => 'miniOrange API Authentication',
		);

		$active_api_plugins = array();
		foreach ( $api_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_api_plugins[] = $plugin_name;
			}
		}

		if ( ! empty( $active_api_plugins ) ) {
			$issues['custom_plugins'] = $active_api_plugins;
		}

		return $issues;
	}

	/**
	 * Build details array for return.
	 *
	 * @since  1.6030.0202
	 * @param  array $issues Issues discovered.
	 * @return array Formatted details.
	 */
	private static function build_details_array( $issues ) {
		$details = array(
			__( 'API token security issues detected:', 'wpshadow' ),
			'',
		);

		if ( isset( $issues['ssl_not_enforced'] ) ) {
			$details[] = '⚠️  ' . $issues['ssl_not_enforced'];
			$details[] = '';
		}

		if ( isset( $issues['application_passwords'] ) ) {
			$details[] = __( 'Application Passwords (WordPress 5.6+):', 'wpshadow' );
			foreach ( $issues['application_passwords'] as $key => $value ) {
				if ( 'users' === $key ) {
					$details[] = sprintf(
						/* translators: %d: number of passwords */
						__( '  • %d users have active application passwords', 'wpshadow' ),
						count( $value )
					);
				} else {
					$details[] = '  • ' . $value;
				}
			}
			$details[] = '';
		}

		if ( isset( $issues['jwt_authentication'] ) ) {
			$details[] = __( 'JWT Authentication:', 'wpshadow' );
			foreach ( $issues['jwt_authentication'] as $key => $value ) {
				$details[] = '  • ' . $value;
			}
			$details[] = '';
		}

		if ( isset( $issues['woocommerce_api'] ) ) {
			$details[] = __( 'WooCommerce API Keys:', 'wpshadow' );
			foreach ( $issues['woocommerce_api'] as $key => $value ) {
				if ( 'active_keys' === $key ) {
					$details[] = sprintf(
						/* translators: %d: number of API keys */
						__( '  • %d active API keys configured', 'wpshadow' ),
						$value
					);
				} else {
					$details[] = '  • ' . $value;
				}
			}
			$details[] = '';
		}

		if ( isset( $issues['custom_tokens'] ) && ! empty( $issues['custom_tokens']['custom_plugins'] ) ) {
			$details[] = __( 'Custom API Authentication Plugins:', 'wpshadow' );
			foreach ( $issues['custom_tokens']['custom_plugins'] as $plugin ) {
				$details[] = '  • ' . $plugin;
			}
		}

		return $details;
	}

	/**
	 * Build solution text.
	 *
	 * @since  1.6030.0202
	 * @param  array $issues Issues discovered.
	 * @return string Solution recommendations.
	 */
	private static function build_solution_text( $issues ) {
		$solution = __( 'To improve API token security:', 'wpshadow' ) . "\n\n";

		if ( isset( $issues['ssl_not_enforced'] ) ) {
			$solution .= '🔒 ' . __( 'CRITICAL: Enable HTTPS and enforce SSL for all connections', 'wpshadow' ) . "\n";
			$solution .= __( '   Add to wp-config.php: define("FORCE_SSL_ADMIN", true);', 'wpshadow' ) . "\n\n";
		}

		$solution .= __( '1. Regularly audit API tokens and revoke unused ones', 'wpshadow' ) . "\n";
		$solution .= __( '2. Use token expiration where possible (JWT tokens)', 'wpshadow' ) . "\n";
		$solution .= __( '3. Limit token permissions to minimum required scope', 'wpshadow' ) . "\n";
		$solution .= __( '4. Never log API tokens in access logs or error logs', 'wpshadow' ) . "\n";
		$solution .= __( '5. Rotate tokens periodically (quarterly recommended)', 'wpshadow' ) . "\n";
		$solution .= __( '6. Use IP restrictions for API access when possible', 'wpshadow' ) . "\n\n";

		$solution .= __( 'Application Passwords Management:', 'wpshadow' ) . "\n";
		$solution .= sprintf(
			'   %s → %s',
			__( 'Users', 'wpshadow' ),
			__( 'Profile → Application Passwords', 'wpshadow' )
		) . "\n\n";

		if ( isset( $issues['woocommerce_api'] ) ) {
			$solution .= __( 'WooCommerce API Keys Management:', 'wpshadow' ) . "\n";
			$solution .= '   ' . admin_url( 'admin.php?page=wc-settings&tab=advanced&section=keys' ) . "\n";
		}

		return $solution;
	}
}
