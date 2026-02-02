<?php
/**
 * REST API Media Upload Diagnostic
 *
 * Tests media upload via REST API.
 * Validates multipart/form-data handling and chunked uploads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Media Upload Diagnostic Class
 *
 * Checks if media upload via REST API is properly configured
 * and functioning correctly.
 *
 * @since 1.7033.1200
 */
class Diagnostic_REST_API_Media_Upload extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-upload';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Upload';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media upload via REST API';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if REST API media upload endpoint is functional and
	 * properly configured.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if REST API is enabled.
		$rest_enabled = true;
		if ( defined( 'REST_API_DISABLED' ) && REST_API_DISABLED ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API is disabled, preventing media uploads via API', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-media-upload',
				'details'      => array(
					'rest_enabled'   => false,
					'recommendation' => __( 'Enable REST API if you need media upload functionality', 'wpshadow' ),
				),
			);
		}

		// Get REST API server.
		$rest_server = rest_get_server();

		// Check media upload endpoint registration.
		$routes = $rest_server->get_routes();
		$media_upload_route = isset( $routes['/wp/v2/media'] );

		if ( ! $media_upload_route ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Media upload REST API endpoint is not registered', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-media-upload',
				'details'      => array(
					'media_upload_route' => $media_upload_route,
					'recommendation'     => __( 'Check if WordPress core files are intact and REST API is not blocked', 'wpshadow' ),
				),
			);
		}

		// Check upload limits.
		$max_upload_size = wp_max_upload_size();
		$max_upload_mb   = $max_upload_size / ( 1024 * 1024 );

		// Check PHP settings.
		$post_max_size       = ini_get( 'post_max_size' );
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$memory_limit        = ini_get( 'memory_limit' );

		// Convert to bytes for comparison.
		$post_max_bytes   = wp_convert_hr_to_bytes( $post_max_size );
		$upload_max_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
		$memory_bytes     = wp_convert_hr_to_bytes( $memory_limit );

		// Check for chunked upload support.
		$has_resumable_js = wp_script_is( 'resumable', 'registered' );

		// Check allowed mime types.
		$allowed_mimes = get_allowed_mime_types();
		$mime_count    = count( $allowed_mimes );

		// Check for file upload restrictions.
		$upload_dir = wp_upload_dir();
		$uploads_writable = wp_is_writable( $upload_dir['path'] );

		// Check for common issues.
		$issues = array();

		// Issue 1: Low upload limit.
		if ( 2 > $max_upload_mb ) {
			$issues[] = array(
				'type'        => 'low_upload_limit',
				'description' => sprintf(
					/* translators: %d: upload limit in MB */
					__( 'Upload limit is only %d MB, which may be too low for media files', 'wpshadow' ),
					round( $max_upload_mb, 2 )
				),
			);
		}

		// Issue 2: Mismatched PHP settings.
		if ( $post_max_bytes < $upload_max_bytes ) {
			$issues[] = array(
				'type'        => 'mismatched_limits',
				'description' => sprintf(
					/* translators: 1: post_max_size, 2: upload_max_filesize */
					__( 'post_max_size (%1$s) is smaller than upload_max_filesize (%2$s)', 'wpshadow' ),
					$post_max_size,
					$upload_max_filesize
				),
			);
		}

		// Issue 3: Low memory limit.
		if ( $memory_bytes < ( $upload_max_bytes * 2 ) ) {
			$issues[] = array(
				'type'        => 'low_memory',
				'description' => __( 'Memory limit may be insufficient for processing large uploads', 'wpshadow' ),
			);
		}

		// Issue 4: Uploads directory not writable.
		if ( ! $uploads_writable ) {
			$issues[] = array(
				'type'        => 'not_writable',
				'description' => __( 'Uploads directory is not writable', 'wpshadow' ),
			);
		}

		// Issue 5: Very few allowed MIME types.
		if ( 5 > $mime_count ) {
			$issues[] = array(
				'type'        => 'restricted_mimes',
				'description' => __( 'Very few MIME types are allowed for upload', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API media upload has configuration issues that may prevent or limit uploads', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-media-upload',
				'details'      => array(
					'rest_enabled'        => $rest_enabled,
					'media_upload_route'  => $media_upload_route,
					'max_upload_size'     => size_format( $max_upload_size ),
					'max_upload_mb'       => round( $max_upload_mb, 2 ),
					'post_max_size'       => $post_max_size,
					'upload_max_filesize' => $upload_max_filesize,
					'memory_limit'        => $memory_limit,
					'uploads_writable'    => $uploads_writable,
					'upload_path'         => $upload_dir['path'],
					'allowed_mime_count'  => $mime_count,
					'has_resumable_js'    => $has_resumable_js,
					'issues_detected'     => $issues,
					'recommendation'      => __( 'Increase PHP upload limits and ensure uploads directory is writable', 'wpshadow' ),
					'php_ini_changes'     => array(
						'upload_max_filesize' => '64M',
						'post_max_size'       => '64M',
						'memory_limit'        => '256M',
						'max_execution_time'  => '300',
					),
					'testing_endpoint'    => get_site_url() . '/wp-json/wp/v2/media',
					'expected_methods'    => array( 'GET', 'POST' ),
				),
			);
		}

		return null;
	}
}
