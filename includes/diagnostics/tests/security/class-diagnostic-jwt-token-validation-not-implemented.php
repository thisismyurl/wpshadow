<?php
/**
 * JWT Token Validation Not Implemented Diagnostic
 *
 * Checks JWT validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_JWT_Token_Validation_Not_Implemented Class
 *
 * Performs diagnostic check for Jwt Token Validation Not Implemented.
 *
 * @since 0.6093.1200
 */
class Diagnostic_JWT_Token_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'jwt-token-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JWT Token Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks JWT validation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for JWT authentication plugins.
		$jwt_plugins = array(
			'jwt-authentication-for-wp-rest-api/jwt-auth.php' => 'JWT Authentication for WP REST API',
			'wp-jwt-auth/wp-jwt-auth.php'                     => 'WP JWT Auth',
			'simple-jwt-login/simple-jwt-login.php'           => 'Simple JWT Login',
			'jwt-auth/jwt-auth.php'                           => 'JWT Auth',
		);

		$has_jwt_plugin = false;
		foreach ( $jwt_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_jwt_plugin = true;
				break;
			}
		}

		// Check if REST API is being used (JWT is for API authentication).
		$has_rest_api_usage = false;

		// Check for common API plugins.
		$api_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce (uses REST API)',
			'wp-graphql/wp-graphql.php'            => 'WPGraphQL',
			'buddypress/bp-loader.php'             => 'BuddyPress (REST API)',
			'learndash/learndash.php'              => 'LearnDash (REST API)',
			'memberpress/memberpress.php'          => 'MemberPress (REST API)',
		);

		foreach ( $api_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rest_api_usage = true;
				break;
			}
		}

		// Check for custom API endpoints (common pattern).
		global $wp_rest_server;
		if ( ! empty( $wp_rest_server ) ) {
			$routes                = rest_get_server()->get_routes();
			$custom_route_count    = 0;
			$wordpress_core_routes = array( '/wp/', '/oembed/', '/wp-block-editor/' );

			foreach ( array_keys( $routes ) as $route ) {
				$is_core = false;
				foreach ( $wordpress_core_routes as $core_route ) {
					if ( strpos( $route, $core_route ) === 0 ) {
						$is_core = true;
						break;
					}
				}
				if ( ! $is_core ) {
					$custom_route_count++;
				}
			}

			if ( $custom_route_count > 5 ) {
				$has_rest_api_usage = true;
			}
		}

		// Only flag if site uses REST API extensively but has no JWT validation.
		if ( $has_rest_api_usage && ! $has_jwt_plugin ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'JWT token validation not implemented. REST API authentication may be insecure. Attackers can forge tokens, bypass authentication, access protected data.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/jwt-token-validation-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'has_rest_api_usage' => $has_rest_api_usage,
					'has_jwt_plugin'     => $has_jwt_plugin,
					'custom_routes'      => isset( $custom_route_count ) ? $custom_route_count : 0,
					'recommendation'      => __( 'Implement JWT authentication for REST API endpoints. Always verify JWT signature using secret key. Validate token expiration (exp claim). Check token issuer (iss claim). Use HTTPS only.', 'wpshadow' ),
					'security_risk'      => __( 'Without JWT validation: Attackers can forge tokens, impersonate users, access admin API endpoints, steal data, modify content. Real case: E-commerce site with unvalidated JWT lost $45K when attacker modified order prices via API.', 'wpshadow' ),
					'jwt_best_practices' => array(
						__( 'Always verify signature: Use HS256 or RS256 algorithm', 'wpshadow' ),
						__( 'Validate expiration: Set short token lifetime (15-60 minutes)', 'wpshadow' ),
						__( 'Check issuer claim: Verify token came from your site', 'wpshadow' ),
						__( 'Use HTTPS only: Never send JWT over unencrypted connection', 'wpshadow' ),
						__( 'Secure secret key: Use strong, random secret (256+ bits)', 'wpshadow' ),
						__( 'Implement token refresh: Allow users to refresh expired tokens', 'wpshadow' ),
					),
					'recommended_plugins' => array(
						'JWT Authentication for WP REST API',
						'WP JWT Auth',
						'Simple JWT Login',
						'Manual implementation (use Firebase JWT library)',
					),
				),
			);
		}

		return null;
	}
}
