<?php
/**
 * Upload File Size Limit Diagnostic
 *
 * Checks if upload_max_filesize and post_max_size are properly configured.
 * Detects limits that are too restrictive for typical WordPress usage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload File Size Limit Diagnostic Class
 *
 * Checks for restrictive upload file size limits.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Upload_File_Size_Limit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-file-size-limit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload File Size Limit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates upload_max_filesize and post_max_size PHP settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get PHP upload limits.
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$post_max_size = ini_get( 'post_max_size' );
		$memory_limit = ini_get( 'memory_limit' );

		// Convert to bytes for comparison.
		$upload_max_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
		$post_max_bytes = wp_convert_hr_to_bytes( $post_max_size );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		// Check if upload_max_filesize is too low.
		if ( $upload_max_bytes < 2097152 ) { // Less than 2MB.
			$issues[] = sprintf(
				/* translators: %s: current upload max filesize */
				__( 'upload_max_filesize is %s (very restrictive, should be at least 8MB)', 'wpshadow' ),
				$upload_max_filesize
			);
		} elseif ( $upload_max_bytes < 8388608 ) { // Less than 8MB.
			$issues[] = sprintf(
				/* translators: %s: current upload max filesize */
				__( 'upload_max_filesize is %s (consider increasing to 16MB or higher)', 'wpshadow' ),
				$upload_max_filesize
			);
		}

		// Check if post_max_size is too low.
		if ( $post_max_bytes < 8388608 ) { // Less than 8MB.
			$issues[] = sprintf(
				/* translators: %s: current post max size */
				__( 'post_max_size is %s (should be at least 8MB for uploads)', 'wpshadow' ),
				$post_max_size
			);
		}

		// Check if post_max_size is smaller than upload_max_filesize.
		if ( $post_max_bytes < $upload_max_bytes ) {
			$issues[] = sprintf(
				/* translators: 1: post_max_size, 2: upload_max_filesize */
				__( 'post_max_size (%1$s) is smaller than upload_max_filesize (%2$s) (uploads will fail)', 'wpshadow' ),
				$post_max_size,
				$upload_max_filesize
			);
		}

		// Check if memory_limit is too close to post_max_size.
		if ( $memory_bytes > 0 && $post_max_bytes > 0 ) {
			if ( $memory_bytes < ( $post_max_bytes * 1.5 ) ) {
				$issues[] = sprintf(
					/* translators: 1: memory_limit, 2: post_max_size */
					__( 'memory_limit (%1$s) too close to post_max_size (%2$s) (should be 1.5x higher)', 'wpshadow' ),
					$memory_limit,
					$post_max_size
				);
			}
		}

		// Get WordPress max upload size (accounts for PHP limits).
		$wp_max_upload = wp_max_upload_size();
		
		if ( $wp_max_upload < 2097152 ) { // Less than 2MB.
			$issues[] = sprintf(
				/* translators: %s: WordPress max upload size */
				__( 'WordPress max upload size is %s (very restrictive)', 'wpshadow' ),
				size_format( $wp_max_upload )
			);
		}

		// Check for upload_max_filesize filter modifications.
		$upload_size_filters = $GLOBALS['wp_filter']['upload_size_limit'] ?? null;
		if ( $upload_size_filters && count( $upload_size_filters->callbacks ) > 0 ) {
			// Test what the filtered value is.
			$filtered_size = apply_filters( 'upload_size_limit', $wp_max_upload );
			
			if ( $filtered_size < $wp_max_upload ) {
				$issues[] = sprintf(
					/* translators: 1: filtered size, 2: PHP limit */
					__( 'upload_size_limit filter reduces limit from %2$s to %1$s (plugin restricting uploads)', 'wpshadow' ),
					size_format( $filtered_size ),
					size_format( $wp_max_upload )
				);
			}
		}

		// Check if multisite has lower limits.
		if ( is_multisite() ) {
			$site_upload_space = get_space_allowed();
			$site_used_space = get_space_used();
			
			if ( $site_upload_space > 0 ) {
				$space_available = ( $site_upload_space - $site_used_space ) * 1024 * 1024; // Convert MB to bytes.
				
				if ( $space_available < 10485760 ) { // Less than 10MB.
					$issues[] = sprintf(
						/* translators: %s: available space */
						__( 'Multisite upload space nearly full (only %s available)', 'wpshadow' ),
						size_format( $space_available )
					);
				}
			}

			// Check multisite fileupload_maxk option.
			$network_upload_max = get_site_option( 'fileupload_maxk' );
			if ( $network_upload_max && $network_upload_max < 5120 ) { // Less than 5MB (value is in KB).
				$issues[] = sprintf(
					/* translators: %s: network upload limit */
					__( 'Network upload limit is %sKB (very restrictive for multisite)', 'wpshadow' ),
					$network_upload_max
				);
			}
		}

		// Check for plupload settings that might override limits.
		$plupload_settings = apply_filters( 'plupload_init', array() );
		if ( isset( $plupload_settings['max_file_size'] ) ) {
			$plupload_max = $plupload_settings['max_file_size'];
			
			// plupload uses bytes with 'b' suffix sometimes.
			$plupload_bytes = is_numeric( $plupload_max ) ? (int) $plupload_max : wp_convert_hr_to_bytes( $plupload_max );
			
			if ( $plupload_bytes < $wp_max_upload ) {
				$issues[] = sprintf(
					/* translators: 1: plupload limit, 2: PHP limit */
					__( 'Plupload max_file_size (%1$s) is less than PHP limit (%2$s)', 'wpshadow' ),
					size_format( $plupload_bytes ),
					size_format( $wp_max_upload )
				);
			}
		}

		// Check if uploads directory has sufficient disk space.
		$upload_dir = wp_upload_dir();
		if ( ! $upload_dir['error'] ) {
			$disk_free_space = @disk_free_space( $upload_dir['basedir'] );
			
			if ( $disk_free_space !== false && $disk_free_space < 104857600 ) { // Less than 100MB.
				$issues[] = sprintf(
					/* translators: %s: available disk space */
					__( 'Upload directory has only %s disk space remaining (uploads may fail)', 'wpshadow' ),
					size_format( $disk_free_space )
				);
			}
		}

		// Check for very large upload_max_filesize (potential security issue).
		if ( $upload_max_bytes > 536870912 ) { // Greater than 512MB.
			$issues[] = sprintf(
				/* translators: %s: upload max filesize */
				__( 'upload_max_filesize is %s (extremely high, potential security risk)', 'wpshadow' ),
				$upload_max_filesize
			);
		}

		// Check for inconsistent limits across PHP settings.
		$max_input_vars = ini_get( 'max_input_vars' );
		if ( $max_input_vars && (int) $max_input_vars < 1000 && $upload_max_bytes > 10485760 ) {
			$issues[] = sprintf(
				/* translators: %s: max_input_vars value */
				__( 'max_input_vars is %s (may cause issues with large media uploads)', 'wpshadow' ),
				$max_input_vars
			);
		}

		// Check if file_uploads is disabled.
		$file_uploads = ini_get( 'file_uploads' );
		if ( ! $file_uploads || '0' === $file_uploads || 'off' === strtolower( $file_uploads ) ) {
			$issues[] = __( 'file_uploads is disabled in PHP (all uploads will fail)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upload-file-size-limit',
			);
		}

		return null;
	}
}
