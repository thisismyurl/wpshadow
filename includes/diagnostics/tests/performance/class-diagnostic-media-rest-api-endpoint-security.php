<?php
/**
 * Media REST API Endpoint Security Diagnostic
 *
 * Checks if REST API media endpoints have proper authentication and permissions.
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
 * Media REST API Endpoint Security Diagnostic Class
 *
 * Verifies that WordPress REST API media endpoints have proper
 * authentication, capability checks, and permission validation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Rest_Api_Endpoint_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-rest-api-endpoint-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media REST API Endpoint Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API media endpoints have proper authentication and permissions';

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
		if ( ! get_option( 'permalink_structure' ) ) {
			$issues[] = __( 'REST API may not function properly without permalinks enabled', 'wpshadow' );
		}

		// Check if media endpoint is registered.
		$media_endpoint = rest_get_server()->get_routes()[ '/wp/v2/media' ] ?? null;
		if ( empty( $media_endpoint ) ) {
			$issues[] = __( 'Media REST API endpoint is not registered', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-rest-api-endpoint-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if authentication is required for POST/DELETE.
		$post_endpoint = $media_endpoint[0] ?? null;
		if ( empty( $post_endpoint['permission_callback'] ) ) {
			$issues[] = __( 'Media POST endpoint does not have a permission callback', 'wpshadow' );
		}

		// Check for REST API authentication methods.
		$auth_methods = array();
		if ( has_filter( 'rest_authentication_errors' ) ) {
			$auth_methods[] = 'custom';
		}
		if ( defined( 'JWT_AUTH_SECRET_KEY' ) ) {
			$auth_methods[] = 'JWT';
		}
		if ( is_ssl() ) {
			$auth_methods[] = 'cookie';
		}

		if ( empty( $auth_methods ) ) {
			$issues[] = __( 'No REST API authentication methods detected', 'wpshadow' );
		}

		// Check if REST API is completely disabled.
		$rest_disabled = has_filter( 'rest_authentication_errors', '__return_true' );
		if ( $rest_disabled ) {
			$issues[] = __( 'REST API is completely disabled', 'wpshadow' );
		}

		// Check for file upload restrictions.
		$max_upload_size = wp_max_upload_size();
		if ( $max_upload_size > 100 * MB_IN_BYTES ) {
			$issues[] = sprintf(
				/* translators: %s: maximum upload size */
				__( 'Maximum upload size is very high (%s), which may allow large malicious files', 'wpshadow' ),
				size_format( $max_upload_size )
			);
		}

		// Check for allowed file types.
		$allowed_mimes = get_allowed_mime_types();
		$dangerous_types = array( 'php', 'exe', 'sh', 'bat', 'cmd' );
		foreach ( $allowed_mimes as $ext => $mime ) {
			$extensions = explode( '|', $ext );
			foreach ( $extensions as $extension ) {
				if ( in_array( $extension, $dangerous_types, true ) ) {
					$issues[] = sprintf(
						/* translators: %s: file extension */
						__( 'Dangerous file type allowed: %s', 'wpshadow' ),
						$extension
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-rest-api-endpoint-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
