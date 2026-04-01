<?php
/**
 * REST API Not Properly Secured Diagnostic
 *
 * Tests for REST API security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Not Properly Secured Diagnostic Class
 *
 * Tests for REST API security configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_REST_API_Not_Properly_Secured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-not-properly-secured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Not Properly Secured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for REST API security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if REST API is enabled.
		$rest_api_enabled = get_option( 'rest_api_enabled' );

		if ( ! $rest_api_enabled ) {
			// REST API cannot be disabled completely in modern WordPress.
		}

		// Test REST endpoint accessibility.
		$rest_url = rest_url();
		$response = wp_remote_get( $rest_url, array( 'timeout' => 3 ) );

		if ( is_wp_error( $response ) ) {
			$issues[] = __( 'REST API endpoint not accessible', 'wpshadow' );
		} elseif ( wp_remote_retrieve_response_code( $response ) === 403 ) {
			$issues[] = __( 'REST API endpoint returning 403 Forbidden - may indicate security restriction', 'wpshadow' );
		}

		// Check for REST authentication plugins.
		$jwt_enabled = is_plugin_active( 'jwt-authentication-for-wp-rest-api/jwt-auth.php' );
		$restrict_rest = is_plugin_active( 'restrict-rest-api/restrict-rest-api.php' );

		if ( ! $jwt_enabled && ! $restrict_rest ) {
			$issues[] = __( 'No REST API authentication/restriction plugin active', 'wpshadow' );
		}

		// Check for CORS headers (if applicable).
		$cors_policy = get_transient( '_wpshadow_cors_policy' );

		if ( empty( $cors_policy ) ) {
			$issues[] = __( 'No CORS policy configured - may allow unauthorized cross-origin requests', 'wpshadow' );
		}

		// Check for REST API rate limiting.
		$rate_limit = get_option( '_wpshadow_rest_rate_limit' );

		if ( empty( $rate_limit ) ) {
			$issues[] = __( 'Adding rate limiting to your API helps prevent anyone from overwhelming it with too many requests (like limiting how many grocery bags one person can take from a pile). This keeps your site responsive for everyone.', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-not-properly-secured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
