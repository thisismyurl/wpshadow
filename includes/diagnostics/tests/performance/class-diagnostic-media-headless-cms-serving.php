<?php
/**
 * Media Headless CMS Serving Diagnostic
 *
 * Checks if media is properly exposed for headless/decoupled WordPress usage.
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
 * Media Headless CMS Serving Diagnostic Class
 *
 * Verifies that media files and metadata are properly exposed via REST API
 * for headless WordPress implementations with proper CORS and authentication.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Headless_Cms_Serving extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-headless-cms-serving';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Headless CMS Serving';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media is properly exposed for headless/decoupled WordPress usage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if REST API is enabled.
		if ( ! get_option( 'permalink_structure' ) ) {
			$issues[] = __( 'Permalinks must be enabled for REST API functionality', 'wpshadow' );
		}

		// Check if media endpoint exists.
		$server = rest_get_server();
		$routes = $server ? $server->get_routes() : array();
		$media_route = $routes['/wp/v2/media'] ?? null;

		if ( empty( $media_route ) ) {
			$issues[] = __( 'Media REST API endpoint is not available', 'wpshadow' );
		}

		// Check for CORS headers support.
		$has_cors_filter = has_filter( 'rest_pre_serve_request' );
		if ( ! $has_cors_filter ) {
			$issues[] = __( 'No CORS configuration detected for REST API', 'wpshadow' );
		}

		// Check if media details include necessary metadata.
		$sample_attachment = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $sample_attachment ) ) {
			$attachment_id = $sample_attachment[0]->ID;
			$rest_controller = new \WP_REST_Attachments_Controller( 'attachment' );
			$request = new \WP_REST_Request( 'GET', "/wp/v2/media/{$attachment_id}" );
			$response = $rest_controller->get_item( $request );

			if ( is_wp_error( $response ) ) {
				$issues[] = __( 'Unable to fetch attachment via REST API', 'wpshadow' );
			} else {
				$data = $response->get_data();

				// Check if essential fields are present.
				$required_fields = array( 'source_url', 'media_type', 'media_details' );
				foreach ( $required_fields as $field ) {
					if ( ! isset( $data[ $field ] ) ) {
						$issues[] = sprintf(
							/* translators: %s: field name */
							__( 'Required field "%s" missing from media REST response', 'wpshadow' ),
							$field
						);
					}
				}
			}
		}

		// Check for authentication mechanisms.
		$auth_plugins = array(
			'jwt-authentication-for-wp-rest-api/jwt-auth.php',
			'wp-rest-api-authentication/wp-rest-api-authentication.php',
			'application-passwords/application-passwords.php',
		);

		$has_auth = false;
		foreach ( $auth_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_auth = true;
				break;
			}
		}

		// Check for Application Passwords (WordPress 5.6+).
		if ( function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available() ) {
			$has_auth = true;
		}

		if ( ! $has_auth ) {
			$issues[] = __( 'No REST API authentication mechanism detected for secure headless access', 'wpshadow' );
		}

		// Check for GraphQL integration (optional but common in headless).
		$has_graphql = class_exists( 'WPGraphQL' );
		if ( ! $has_graphql ) {
			// Not an issue, just informational.
			// Could add to description if needed.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-headless-cms-serving',
			);
		}

		return null;
	}
}
