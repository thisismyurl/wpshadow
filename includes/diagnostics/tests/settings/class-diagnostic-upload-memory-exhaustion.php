<?php
/**
 * Upload Memory Exhaustion Diagnostic
 *
 * Detects memory limit issues during file uploads. Tests memory_limit against
 * typical file sizes and image processing requirements.
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
 * Upload Memory Exhaustion Diagnostic Class
 *
 * Checks for memory limit issues during uploads.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upload_Memory_Exhaustion extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-memory-exhaustion';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Memory Exhaustion';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates memory_limit is sufficient for file uploads and image processing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get PHP memory limit.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		// Get upload max filesize for comparison.
		$upload_max = ini_get( 'upload_max_filesize' );
		$upload_bytes = wp_convert_hr_to_bytes( $upload_max );

		// Check if memory limit is disabled (-1).
		if ( -1 === $memory_bytes ) {
			// Unlimited memory, no issues.
			return null;
		}

		// Check if memory limit is very low.
		if ( $memory_bytes < 33554432 ) { // Less than 32MB.
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit is %s (very low, uploads and image processing will fail)', 'wpshadow' ),
				$memory_limit
			);
		} elseif ( $memory_bytes < 67108864 ) { // Less than 64MB.
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit is %s (low, may fail on large images)', 'wpshadow' ),
				$memory_limit
			);
		}

		// Image processing requires significant memory.
		// Rule of thumb: 5x the image file size for processing.
		$recommended_memory = $upload_bytes * 5;
		
		if ( $memory_bytes < $recommended_memory && $upload_bytes > 1048576 ) {
			$issues[] = sprintf(
				/* translators: 1: current memory, 2: recommended memory */
				__( 'memory_limit (%1$s) should be 5x upload_max_filesize (recommended: %2$s)', 'wpshadow' ),
				$memory_limit,
				size_format( $recommended_memory )
			);
		}

		// Check current memory usage.
		$current_usage = memory_get_usage( true );
		$percent_used = ( $current_usage / $memory_bytes ) * 100;
		
		if ( $percent_used > 80 ) {
			$issues[] = sprintf(
				/* translators: 1: percentage, 2: current usage, 3: limit */
				__( 'Already using %1$s%% of memory limit (%2$s of %3$s)', 'wpshadow' ),
				number_format( $percent_used, 1 ),
				size_format( $current_usage ),
				$memory_limit
			);
		}

		// Check peak memory usage.
		$peak_usage = memory_get_peak_usage( true );
		$peak_percent = ( $peak_usage / $memory_bytes ) * 100;
		
		if ( $peak_percent > 90 ) {
			$issues[] = sprintf(
				/* translators: 1: peak usage, 2: limit */
				__( 'Peak memory usage %1$s approaches limit of %2$s (exhaustion risk)', 'wpshadow' ),
				size_format( $peak_usage ),
				$memory_limit
			);
		}

		// Check for wp_raise_memory_limit functionality.
		$admin_memory = @ini_get( 'memory_limit' );
		wp_raise_memory_limit( 'admin' );
		$raised_memory = @ini_get( 'memory_limit' );
		
		if ( $admin_memory === $raised_memory && $memory_bytes < 268435456 ) { // Less than 256MB.
			$issues[] = __( 'Cannot raise memory limit for admin tasks (WP_MAX_MEMORY_LIMIT may be needed)', 'wpshadow' );
		}

		// Check WP_MEMORY_LIMIT constant.
		if ( defined( 'WP_MEMORY_LIMIT' ) ) {
			$wp_memory = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
			
			if ( $wp_memory < $memory_bytes ) {
				$issues[] = sprintf(
					/* translators: 1: WP_MEMORY_LIMIT, 2: PHP memory_limit */
					__( 'WP_MEMORY_LIMIT (%1$s) is less than PHP memory_limit (%2$s)', 'wpshadow' ),
					WP_MEMORY_LIMIT,
					$memory_limit
				);
			}
			
			if ( $wp_memory < 67108864 ) { // Less than 64MB.
				$issues[] = sprintf(
					/* translators: %s: WP_MEMORY_LIMIT */
					__( 'WP_MEMORY_LIMIT is %s (increase to at least 128M)', 'wpshadow' ),
					WP_MEMORY_LIMIT
				);
			}
		}

		// Check WP_MAX_MEMORY_LIMIT constant (for admin).
		if ( defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
			$wp_max_memory = wp_convert_hr_to_bytes( WP_MAX_MEMORY_LIMIT );
			
			if ( $wp_max_memory < 268435456 ) { // Less than 256MB.
				$issues[] = sprintf(
					/* translators: %s: WP_MAX_MEMORY_LIMIT */
					__( 'WP_MAX_MEMORY_LIMIT is %s (should be at least 256M for image processing)', 'wpshadow' ),
					WP_MAX_MEMORY_LIMIT
				);
			}
		} else {
			$issues[] = __( 'WP_MAX_MEMORY_LIMIT not defined (consider setting to 256M)', 'wpshadow' );
		}

		// Check for image editor (GD vs Imagick).
		$image_editors = apply_filters( 'wp_image_editors', array( 'WP_Image_Editor_Imagick', 'WP_Image_Editor_GD' ) );
		
		if ( in_array( 'WP_Image_Editor_GD', $image_editors, true ) && ! in_array( 'WP_Image_Editor_Imagick', $image_editors, true ) ) {
			// GD uses more memory than Imagick.
			if ( $memory_bytes < 134217728 ) { // Less than 128MB.
				$issues[] = __( 'Using GD image editor with low memory (Imagick is more efficient)', 'wpshadow' );
			}
		}

		// Test actual memory availability for image processing.
		$test_image_size = 4000 * 3000 * 3; // 12MP image, 3 bytes per pixel.
		$estimated_memory = $test_image_size *1.0; // Processing overhead.
		
		if ( $memory_bytes < $estimated_memory ) {
			$issues[] = sprintf(
				/* translators: %s: recommended memory */
				__( 'Large images (12MP+) will likely fail, increase to %s', 'wpshadow' ),
				size_format( $estimated_memory )
			);
		}

		// Check for image_memory_limit filter.
		$image_memory_filters = $GLOBALS['wp_filter']['image_memory_limit'] ?? null;
		if ( $image_memory_filters && count( $image_memory_filters->callbacks ) > 0 ) {
			// Test what the filtered value is.
			$filtered_memory = apply_filters( 'image_memory_limit', $memory_limit );
			$filtered_bytes = wp_convert_hr_to_bytes( $filtered_memory );
			
			if ( $filtered_bytes < $memory_bytes ) {
				$issues[] = sprintf(
					/* translators: 1: filtered limit, 2: PHP limit */
					__( 'image_memory_limit filter reduces from %2$s to %1$s', 'wpshadow' ),
					$filtered_memory,
					$memory_limit
				);
			}
		}

		// Check for wp_image_editor_before_change action (memory-intensive operations).
		global $wpdb;
		
		$recent_media = $wpdb->get_results(
			"SELECT ID, post_title
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE 'image/%'
			ORDER BY post_date DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $recent_media ) ) {
			$large_images = 0;
			
			foreach ( $recent_media as $attachment ) {
				$file_path = get_attached_file( (int) $attachment['ID'] );
				
				if ( $file_path && file_exists( $file_path ) ) {
					$file_size = filesize( $file_path );
					
					// Flag images over 5MB as potentially problematic.
					if ( $file_size > 5242880 ) {
						++$large_images;
					}
				}
			}
			
			if ( $large_images > 3 && $memory_bytes < 134217728 ) {
				$issues[] = sprintf(
					/* translators: %d: number of large images */
					__( '%d recent uploads over 5MB detected, increase memory for processing', 'wpshadow' ),
					$large_images
				);
			}
		}

		// Check for memory_limit filter modifications.
		$memory_filters = $GLOBALS['wp_filter']['admin_memory_limit'] ?? null;
		if ( $memory_filters && count( $memory_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on admin_memory_limit (verify they increase limit)', 'wpshadow' ),
				count( $memory_filters->callbacks )
			);
		}

		// Check if image sizes are generating properly (memory exhaustion symptom).
		if ( ! empty( $recent_media ) ) {
			$missing_sizes = 0;
			
			foreach ( array_slice( $recent_media, 0, 5 ) as $attachment ) {
				$metadata = wp_get_attachment_metadata( (int) $attachment['ID'] );
				
				if ( $metadata && isset( $metadata['sizes'] ) ) {
					$registered_sizes = get_intermediate_image_sizes();
					$generated_sizes = count( $metadata['sizes'] );
					
					// If significantly fewer sizes than registered, likely memory issue.
					if ( $generated_sizes < ( count( $registered_sizes ) / 2 ) && count( $registered_sizes ) > 3 ) {
						++$missing_sizes;
					}
				}
			}
			
			if ( $missing_sizes > 2 ) {
				$issues[] = sprintf(
					/* translators: %d: number of images with missing sizes */
					__( '%d recent uploads missing image sizes (likely memory exhaustion)', 'wpshadow' ),
					$missing_sizes
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upload-memory-exhaustion',
			);
		}

		return null;
	}
}
