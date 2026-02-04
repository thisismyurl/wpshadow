<?php
/**
 * HTTP Upload Errors Diagnostic
 *
 * Detects HTTP errors during upload process by monitoring for
 * 413 (too large), 502 (bad gateway), 504 (timeout) errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP Upload Errors Class
 *
 * Monitors for HTTP errors during file uploads that indicate server
 * configuration issues requiring adjustment.
 *
 * @since 1.6030.2148
 */
class Diagnostic_HTTP_Upload_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-upload-errors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Upload Errors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects HTTP errors during upload process';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for common HTTP errors during uploads and validates
	 * server configuration to prevent upload failures.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if upload errors detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		// Check PHP upload settings vs server limits.
		$upload_max = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$post_max = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );

		$details['upload_max_filesize'] = size_format( $upload_max );
		$details['post_max_size'] = size_format( $post_max );
		$details['memory_limit'] = size_format( $memory_limit );

		// Check if post_max_size is smaller than upload_max_filesize (causes 413 errors).
		if ( $post_max < $upload_max ) {
			$issues[] = sprintf(
				/* translators: 1: post_max_size, 2: upload_max_filesize */
				__( 'post_max_size (%1$s) is smaller than upload_max_filesize (%2$s) - will cause 413 errors', 'wpshadow' ),
				size_format( $post_max ),
				size_format( $upload_max )
			);
		}

		// Check if memory_limit is too low for uploads.
		if ( $memory_limit !== -1 && $memory_limit < ( $upload_max * 2 ) ) {
			$issues[] = sprintf(
				/* translators: 1: memory_limit, 2: recommended amount */
				__( 'memory_limit (%1$s) may be too low for upload processing - recommend %2$s', 'wpshadow' ),
				size_format( $memory_limit ),
				size_format( $upload_max * 2 )
			);
		}

		// Check max_execution_time (causes 504 timeouts).
		$max_execution = (int) ini_get( 'max_execution_time' );

		$details['max_execution_time'] = $max_execution;

		if ( $max_execution > 0 && $max_execution < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: max_execution_time in seconds */
				__( 'max_execution_time is %d seconds - may cause 504 timeout errors on large uploads', 'wpshadow' ),
				$max_execution
			);
		}

		// Check max_input_time.
		$max_input_time = (int) ini_get( 'max_input_time' );

		$details['max_input_time'] = $max_input_time;

		if ( $max_input_time > 0 && $max_input_time < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: max_input_time in seconds */
				__( 'max_input_time is %d seconds - may cause upload timeouts', 'wpshadow' ),
				$max_input_time
			);
		}

		// Check for reverse proxy or load balancer timeouts.
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) || isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$details['behind_proxy'] = true;

			$issues[] = __( 'Site is behind proxy/load balancer - verify proxy timeout settings', 'wpshadow' );
		}

		// Check if mod_security or similar WAF is blocking uploads.
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();

			if ( in_array( 'mod_security2', $modules, true ) || in_array( 'mod_security', $modules, true ) ) {
				$details['mod_security_detected'] = true;

				$issues[] = __( 'ModSecurity detected - may block legitimate uploads with 406/403 errors', 'wpshadow' );
			}
		}

		// Check for failed upload attempts in database.
		global $wpdb;

		$failed_uploads = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_status = 'auto-draft'
			AND post_date < DATE_SUB(NOW(), INTERVAL 1 DAY)"
		);

		if ( $failed_uploads && (int) $failed_uploads > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed uploads */
				_n(
					'Found %d stale auto-draft attachment (indicates failed upload)',
					'Found %d stale auto-draft attachments (indicates failed uploads)',
					(int) $failed_uploads,
					'wpshadow'
				),
				number_format_i18n( (int) $failed_uploads )
			);

			$details['failed_upload_drafts'] = (int) $failed_uploads;
		}

		// Check for chunked upload support (large files).
		$plupload_config = wp_plupload_default_settings();

		if ( isset( $plupload_config['max_file_size'] ) ) {
			$plupload_max = wp_convert_hr_to_bytes( $plupload_config['max_file_size'] );

			$details['plupload_max_file_size'] = size_format( $plupload_max );

			if ( $plupload_max < $upload_max ) {
				$issues[] = sprintf(
					/* translators: 1: plupload limit, 2: server limit */
					__( 'Plupload max file size (%1$s) is lower than server limit (%2$s)', 'wpshadow' ),
					size_format( $plupload_max ),
					size_format( $upload_max )
				);
			}
		}

		// Check for nginx client_max_body_size issues (if detectable).
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stripos( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false ) {
			$details['server_software'] = 'nginx';

			$issues[] = __( 'Nginx detected - verify client_max_body_size is >= post_max_size', 'wpshadow' );
		}

		// Check for HTTP/2 issues with uploads.
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) && stripos( $_SERVER['SERVER_PROTOCOL'], 'HTTP/2' ) !== false ) {
			$details['http_version'] = 'HTTP/2';
		}

		// Check if uploads directory is writable (prevents 500 errors).
		$upload_dir = wp_upload_dir();

		if ( ! wp_is_writable( $upload_dir['path'] ) ) {
			$issues[] = __( 'Upload directory not writable - will cause 500 internal server errors', 'wpshadow' );
			$details['upload_dir_writable'] = false;
		} else {
			$details['upload_dir_writable'] = true;
		}

		// Check for .htaccess restrictions.
		$htaccess_path = ABSPATH . '.htaccess';

		if ( file_exists( $htaccess_path ) && is_readable( $htaccess_path ) ) {
			$htaccess = file_get_contents( $htaccess_path );

			// Check for LimitRequestBody directive.
			if ( preg_match( '/LimitRequestBody\s+(\d+)/', $htaccess, $matches ) ) {
				$limit_bytes = (int) $matches[1];

				if ( $limit_bytes < $upload_max ) {
					$issues[] = sprintf(
						/* translators: 1: .htaccess limit, 2: PHP limit */
						__( '.htaccess LimitRequestBody (%1$s) is lower than upload_max_filesize (%2$s)', 'wpshadow' ),
						size_format( $limit_bytes ),
						size_format( $upload_max )
					);

					$details['htaccess_limit'] = size_format( $limit_bytes );
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => implode( '. ', $issues ),
			'severity'    => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/http-upload-errors',
			'details'     => $details,
		);
	}
}
