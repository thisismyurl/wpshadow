<?php
/**
 * Multipart Upload Failures Diagnostic
 *
 * Tests chunked/multipart upload functionality for large files.
 * Detects configuration issues preventing chunked uploads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multipart_Upload_Failures Class
 *
 * Validates multipart/chunked upload support. Large files are uploaded
 * in chunks via JavaScript (Plupload). Issues with chunk handling cause
 * upload failures for files larger than server limits.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Multipart_Upload_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'multipart-upload-failures';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Multipart Upload Failures';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests chunked/multipart upload functionality for large files';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Plupload configuration
	 * - Chunk size settings
	 * - Server support for chunked uploads
	 * - Failed multipart uploads in database
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get Plupload settings.
		$plupload_settings = wp_plupload_default_settings();

		// Check if multipart is enabled.
		if ( isset( $plupload_settings['multipart'] ) && ! $plupload_settings['multipart'] ) {
			$issues[] = __( 'Multipart uploads are disabled in Plupload configuration', 'wpshadow' );
		}

		// Check chunk size.
		$chunk_size = 0;
		if ( isset( $plupload_settings['max_file_size'] ) ) {
			$chunk_size = wp_convert_hr_to_bytes( $plupload_settings['max_file_size'] );
		}

		// Chunk size should be reasonable (1MB - 10MB optimal).
		if ( $chunk_size > 0 ) {
			$one_mb  = 1024 * 1024;
			$ten_mb  = $one_mb * 10;
			$hundred_mb = $one_mb * 100;

			if ( $chunk_size < $one_mb ) {
				$issues[] = sprintf(
					/* translators: %s: chunk size */
					__( 'Plupload chunk size (%s) is very small - may cause slow uploads', 'wpshadow' ),
					size_format( $chunk_size )
				);
			} elseif ( $chunk_size > $hundred_mb ) {
				$issues[] = sprintf(
					/* translators: %s: chunk size */
					__( 'Plupload chunk size (%s) is very large - chunks may fail', 'wpshadow' ),
					size_format( $chunk_size )
				);
			}
		}

		// Check PHP upload limits.
		$upload_max = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$post_max   = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );

		// For chunked uploads, post_max should be >= chunk size + overhead.
		$min_post_size = $chunk_size + ( 1024 * 100 ); // 100KB overhead for form data.
		if ( $post_max > 0 && $post_max < $min_post_size ) {
			$issues[] = sprintf(
				/* translators: 1: post_max_size, 2: chunk size */
				__( 'post_max_size (%1$s) is too small for chunk size (%2$s) - chunks will fail', 'wpshadow' ),
				size_format( $post_max ),
				size_format( $chunk_size )
			);
		}

		// Check for mod_security - often blocks chunked uploads.
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			if ( in_array( 'mod_security', $modules, true ) || in_array( 'mod_security2', $modules, true ) ) {
				$issues[] = __( 'ModSecurity detected - may block multipart uploads (check SecUploadKeepFiles)', 'wpshadow' );
			}
		}

		// Check for failed chunked uploads in database.
		global $wpdb;

		// Find incomplete/failed uploads (auto-draft attachments with _wp_attachment_partial meta).
		$failed_chunks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'attachment'
				AND p.post_status = 'auto-draft'
				AND pm.meta_key = %s
				AND p.post_date < %s",
				'_wp_attachment_partial',
				gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) )
			)
		);

		if ( $failed_chunks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed uploads */
				_n(
					'%d incomplete chunked upload found (older than 1 day)',
					'%d incomplete chunked uploads found (older than 1 day)',
					$failed_chunks,
					'wpshadow'
				),
				$failed_chunks
			);
		}

		// Check for uploads that failed with "The uploaded file could not be moved" error.
		// These are stored in transients.
		$move_errors = 0;
		$transients  = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value LIKE %s",
				$wpdb->esc_like( '_transient_upload_error_' ) . '%',
				'%could not be moved%'
			)
		);
		$move_errors = count( $transients );

		if ( $move_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed moves */
				_n(
					'%d upload failed due to file move errors',
					'%d uploads failed due to file move errors',
					$move_errors,
					'wpshadow'
				),
				$move_errors
			);
		}

		// Check upload directory permissions.
		$upload_dir = wp_upload_dir();
		if ( ! wp_is_writable( $upload_dir['path'] ) ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Upload directory not writable: %s', 'wpshadow' ),
				$upload_dir['path']
			);
		}

		// Check for .htaccess rules that might block chunk uploads.
		$htaccess_file = $upload_dir['basedir'] . '/.htaccess';
		if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
			$htaccess = file_get_contents( $htaccess_file );
			
			// Check for strict file type restrictions.
			if ( false !== strpos( $htaccess, 'FilesMatch' ) && false !== strpos( $htaccess, 'deny from all' ) ) {
				$issues[] = __( '.htaccess in uploads directory may block chunked uploads', 'wpshadow' );
			}
		}

		// Check for nginx client_body_temp_path issues.
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) ) {
			// On nginx, chunked uploads require proper client_body_temp_path.
			// We can't directly check this, but we can check for common errors.
			$nginx_errors = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->posts} p
					WHERE p.post_type = 'attachment'
					AND p.post_status = 'auto-draft'
					AND p.post_title LIKE %s
					AND p.post_date > %s",
					'%upload%',
					gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
				)
			);

			if ( $nginx_errors > 10 ) {
				$issues[] = __( 'Multiple failed uploads on nginx - check client_body_temp_path configuration', 'wpshadow' );
			}
		}

		// Check max_execution_time - chunks need time to process.
		$max_execution = (int) ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'max_execution_time (%d seconds) is low - chunked uploads may timeout', 'wpshadow' ),
				$max_execution
			);
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with multipart/chunked upload functionality',
						'%d issues detected with multipart/chunked upload functionality',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multipart-upload-failures',
				'details'      => array(
					'issues'            => $issues,
					'plupload_settings' => $plupload_settings,
					'chunk_size'        => $chunk_size > 0 ? size_format( $chunk_size ) : 'N/A',
					'upload_max'        => size_format( $upload_max ),
					'post_max'          => size_format( $post_max ),
					'failed_chunks'     => $failed_chunks,
					'move_errors'       => $move_errors,
				),
			);
		}

		return null;
	}
}
