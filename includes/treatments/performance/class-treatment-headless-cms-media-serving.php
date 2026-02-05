<?php
/**
 * Headless CMS Media Serving Treatment
 *
 * Tests media delivery for headless WordPress setups.
 * Validates CORS and authentication configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Headless CMS Media Serving Treatment Class
 *
 * Checks if media is properly configured for headless WordPress
 * with appropriate CORS and authentication.
 *
 * @since 1.7033.1200
 */
class Treatment_Headless_CMS_Media_Serving extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'headless-cms-media-serving';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Headless CMS Media Serving';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media delivery for headless WordPress setups';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress is configured for headless CMS usage
	 * with proper CORS and media serving.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if REST API is enabled (required for headless).
		$rest_enabled = true;
		if ( defined( 'REST_API_DISABLED' ) && REST_API_DISABLED ) {
			return null; // Not using headless if REST is disabled.
		}

		// Check for CORS headers.
		$site_url = get_site_url();
		$test_url = $site_url . '/wp-json/wp/v2/media?per_page=1';

		$response = wp_remote_get(
			$test_url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Origin' => 'https://example.com',
				),
			)
		);

		$has_cors = false;
		$cors_headers = array();

		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );

			// Check for CORS headers.
			if ( isset( $headers['access-control-allow-origin'] ) ) {
				$has_cors = true;
				$cors_headers['allow_origin'] = $headers['access-control-allow-origin'];
			}

			if ( isset( $headers['access-control-allow-methods'] ) ) {
				$cors_headers['allow_methods'] = $headers['access-control-allow-methods'];
			}

			if ( isset( $headers['access-control-allow-credentials'] ) ) {
				$cors_headers['allow_credentials'] = $headers['access-control-allow-credentials'];
			}
		}

		// Check for headless-specific plugins.
		$headless_plugins = array(
			'wp-graphql/wp-graphql.php'               => 'WPGraphQL',
			'wp-api-menus/wp-api-menus.php'           => 'WP REST API Menus',
			'jwt-authentication-for-wp-rest-api/jwt-auth.php' => 'JWT Authentication',
			'wp-jamstack-deployments/wp-jamstack-deployments.php' => 'WP Jamstack Deployments',
		);

		$has_headless_plugin = false;
		$active_headless = array();
		foreach ( $headless_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_headless_plugin = true;
				$active_headless[] = $name;
			}
		}

		// Check for custom REST routes that might indicate headless usage.
		$rest_server = rest_get_server();
		$routes = $rest_server->get_routes();

		$has_graphql = isset( $routes['/graphql'] ) || isset( $routes['/wp/graphql'] );

		// Check for CDN configuration (common in headless).
		$has_cdn_constant = defined( 'WP_CONTENT_URL' ) && 
		                     false !== strpos( WP_CONTENT_URL, 'cdn' );

		// Test media URL accessibility.
		$test_image = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
			)
		);

		$media_accessible = false;
		$media_cors = false;

		if ( ! empty( $test_image ) ) {
			$image_url = wp_get_attachment_url( $test_image[0]->ID );

			$image_response = Treatment_Request_Helper::head_result(
				$image_url,
				array(
					'timeout' => 5,
					'headers' => array(
						'Origin' => 'https://example.com',
					),
				)
			);

			if ( $image_response['success'] ) {
				$status = (int) $image_response['code'];
				$media_accessible = 200 === $status;

				$image_headers = wp_remote_retrieve_headers( $image_response['response'] );
				$media_cors = isset( $image_headers['access-control-allow-origin'] );
			}
		}

		// Only flag if it appears to be headless but misconfigured.
		$appears_headless = $has_headless_plugin || $has_graphql;

		if ( $appears_headless && ( ! $has_cors || ! $media_cors ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress appears configured for headless CMS but CORS headers are missing or misconfigured', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/headless-cms-media-serving',
				'details'      => array(
					'appears_headless'    => $appears_headless,
					'has_cors'            => $has_cors,
					'cors_headers'        => $cors_headers,
					'media_cors'          => $media_cors,
					'media_accessible'    => $media_accessible,
					'has_headless_plugin' => $has_headless_plugin,
					'active_headless'     => $active_headless,
					'has_graphql'         => $has_graphql,
					'has_cdn_constant'    => $has_cdn_constant,
					'tested_api_url'      => $test_url,
					'issue'               => __( 'Without CORS headers, frontend applications cannot access media via REST API', 'wpshadow' ),
					'recommendation'      => __( 'Configure CORS headers to allow frontend domain to access media', 'wpshadow' ),
					'cors_setup_code'     => "add_action( 'rest_api_init', function() {\n    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );\n    add_filter( 'rest_pre_serve_request', function( \$value ) {\n        header( 'Access-Control-Allow-Origin: https://your-frontend-domain.com' );\n        header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );\n        header( 'Access-Control-Allow-Credentials: true' );\n        return \$value;\n    });\n}, 15 );",
					'security_note'       => __( 'Always specify exact origins in CORS headers, never use wildcard (*) with credentials', 'wpshadow' ),
					'headless_checklist'  => array(
						__( '✓ REST API enabled', 'wpshadow' ),
						$has_cors ? __( '✓ CORS headers configured', 'wpshadow' ) : __( '✗ CORS headers missing', 'wpshadow' ),
						$media_cors ? __( '✓ Media files have CORS', 'wpshadow' ) : __( '✗ Media CORS missing', 'wpshadow' ),
						$has_cdn_constant ? __( '✓ CDN configured', 'wpshadow' ) : __( '○ CDN optional', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
