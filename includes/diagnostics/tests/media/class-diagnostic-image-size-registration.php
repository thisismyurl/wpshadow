<?php
/**
 * Image Size Registration Diagnostic
 *
 * Validates all registered image sizes are generated correctly. Tests add_image_size functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Size_Registration Class
 *
 * Validates registered image sizes and their generation. WordPress allows
 * custom image sizes via add_image_size(). Issues occur when sizes are
 * registered but not generated, or have invalid dimensions.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Size_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-size-registration';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Size Registration';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates all registered image sizes are generated correctly';

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
	 * - Registered image sizes configuration
	 * - Missing image size files
	 * - Invalid dimensions
	 * - Duplicate size names
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $_wp_additional_image_sizes;
		$issues = array();

		// Get all registered image sizes.
		$registered_sizes = array();
		
		// Get core sizes.
		$core_sizes = array(
			'thumbnail' => array(
				'width'  => get_option( 'thumbnail_size_w', 150 ),
				'height' => get_option( 'thumbnail_size_h', 150 ),
				'crop'   => get_option( 'thumbnail_crop', false ),
			),
			'medium' => array(
				'width'  => get_option( 'medium_size_w', 300 ),
				'height' => get_option( 'medium_size_h', 300 ),
				'crop'   => false,
			),
			'medium_large' => array(
				'width'  => get_option( 'medium_large_size_w', 768 ),
				'height' => get_option( 'medium_large_size_h', 0 ),
				'crop'   => false,
			),
			'large' => array(
				'width'  => get_option( 'large_size_w', 1024 ),
				'height' => get_option( 'large_size_h', 1024 ),
				'crop'   => false,
			),
		);

		// Merge with custom sizes.
		if ( isset( $_wp_additional_image_sizes ) && is_array( $_wp_additional_image_sizes ) ) {
			$registered_sizes = array_merge( $core_sizes, $_wp_additional_image_sizes );
		} else {
			$registered_sizes = $core_sizes;
		}

		// Check for invalid dimensions.
		foreach ( $registered_sizes as $size_name => $size_data ) {
			$width  = isset( $size_data['width'] ) ? (int) $size_data['width'] : 0;
			$height = isset( $size_data['height'] ) ? (int) $size_data['height'] : 0;

			if ( $width <= 0 && $height <= 0 ) {
				$issues[] = sprintf(
					/* translators: %s: size name */
					__( 'Image size "%s" has invalid dimensions (both width and height are 0)', 'wpshadow' ),
					$size_name
				);
			}

			// Check for unreasonably large dimensions.
			if ( $width > 10000 || $height > 10000 ) {
				$issues[] = sprintf(
					/* translators: 1: size name, 2: width, 3: height */
					__( 'Image size "%1$s" has very large dimensions (%2$d x %3$d) - may cause memory issues', 'wpshadow' ),
					$size_name,
					$width,
					$height
				);
			}

			// Check for duplicate names with different dimensions.
			$matching_sizes = array_filter(
				$registered_sizes,
				function( $data ) use ( $size_data, $size_name ) {
					return $size_name !== $data 
						&& isset( $data['width'] ) 
						&& isset( $data['height'] )
						&& $data['width'] === $size_data['width']
						&& $data['height'] === $size_data['height'];
				}
			);

			if ( count( $matching_sizes ) > 1 ) {
				$issues[] = sprintf(
					/* translators: %s: size name */
					__( 'Image size "%s" has duplicate dimensions with another size - redundant', 'wpshadow' ),
					$size_name
				);
			}
		}

		// Check for images missing registered sizes.
		global $wpdb;

		// Get recent images and check their metadata.
		$recent_images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				LIMIT 10",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		$missing_size_count = 0;
		$missing_sizes      = array();

		foreach ( $recent_images as $image ) {
			$metadata = maybe_unserialize( $image->meta_value );
			
			if ( ! is_array( $metadata ) || empty( $metadata['sizes'] ) ) {
				continue;
			}

			// Check which registered sizes are missing.
			foreach ( $registered_sizes as $size_name => $size_data ) {
				if ( ! isset( $metadata['sizes'][ $size_name ] ) ) {
					$missing_size_count++;
					$missing_sizes[ $size_name ] = isset( $missing_sizes[ $size_name ] ) ? $missing_sizes[ $size_name ] + 1 : 1;
				}
			}
		}

		if ( $missing_size_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of missing sizes */
				_n(
					'%d registered image size is not being generated for recent uploads',
					'%d registered image sizes are not being generated for recent uploads',
					count( $missing_sizes ),
					'wpshadow'
				),
				count( $missing_sizes )
			);
		}

		// Check for orphaned image size files (sizes no longer registered).
		$upload_dir = wp_upload_dir();
		
		if ( wp_is_writable( $upload_dir['path'] ) ) {
			// Sample check: look for common orphaned patterns.
			$orphaned_patterns = array();
			
			// This is a performance consideration - we check a sample.
			$sample_images = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, pm.meta_value
					FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
					WHERE p.post_type = 'attachment'
					AND p.post_mime_type LIKE %s
					LIMIT 5",
					'image/%'
				)
			);

			foreach ( $sample_images as $image ) {
				$metadata = maybe_unserialize( $image->meta_value );
				
				if ( ! is_array( $metadata ) || empty( $metadata['sizes'] ) ) {
					continue;
				}

				// Check for sizes in metadata that aren't registered.
				foreach ( $metadata['sizes'] as $size_name => $size_data ) {
					if ( ! isset( $registered_sizes[ $size_name ] ) ) {
						$orphaned_patterns[ $size_name ] = true;
					}
				}
			}

			if ( ! empty( $orphaned_patterns ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of orphaned sizes */
					_n(
						'%d orphaned image size detected (no longer registered but files exist)',
						'%d orphaned image sizes detected (no longer registered but files exist)',
						count( $orphaned_patterns ),
						'wpshadow'
					),
					count( $orphaned_patterns )
				);
			}
		}

		// Check if intermediate_image_sizes filter is being used.
		$filter_priority = has_filter( 'intermediate_image_sizes' );
		if ( false !== $filter_priority ) {
			$issues[] = __( 'intermediate_image_sizes filter is active - may prevent some sizes from generating', 'wpshadow' );
		}

		// Check for theme/plugin conflicts.
		$active_plugins = get_option( 'active_plugins', array() );
		$known_conflicts = array(
			'regenerate-thumbnails' => __( 'May have regenerated sizes with different settings', 'wpshadow' ),
			'force-regenerate-thumbnails' => __( 'May have altered size generation', 'wpshadow' ),
		);

		foreach ( $known_conflicts as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = sprintf(
						/* translators: %s: conflict message */
						__( 'Plugin note: %s', 'wpshadow' ),
						$message
					);
					break;
				}
			}
		}

		// Check memory limit for large image size generation.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$max_width    = 0;
		$max_height   = 0;

		foreach ( $registered_sizes as $size_data ) {
			if ( isset( $size_data['width'] ) && $size_data['width'] > $max_width ) {
				$max_width = $size_data['width'];
			}
			if ( isset( $size_data['height'] ) && $size_data['height'] > $max_height ) {
				$max_height = $size_data['height'];
			}
		}

		// Estimate memory needed (rough calculation: width * height * 5).
		$estimated_memory = $max_width * $max_height * 5;
		
		if ( $memory_limit > 0 && $memory_limit < $estimated_memory ) {
			$issues[] = sprintf(
				/* translators: 1: memory limit, 2: estimated need */
				__( 'memory_limit (%1$s) may be insufficient for largest image size (estimated %2$s needed)', 'wpshadow' ),
				size_format( $memory_limit ),
				size_format( $estimated_memory )
			);
		}

		// Check for BIG_IMAGE_SIZE_THRESHOLD (WordPress 5.3+).
		if ( function_exists( 'wp_get_additional_image_sizes' ) ) {
			$threshold = apply_filters( 'big_image_size_threshold', 2560 );
			
			foreach ( $registered_sizes as $size_name => $size_data ) {
				$width  = isset( $size_data['width'] ) ? (int) $size_data['width'] : 0;
				$height = isset( $size_data['height'] ) ? (int) $size_data['height'] : 0;
				
				if ( $width > $threshold || $height > $threshold ) {
					$issues[] = sprintf(
						/* translators: 1: size name, 2: threshold */
						__( 'Image size "%1$s" exceeds big_image_size_threshold (%2$d) - may be scaled down', 'wpshadow' ),
						$size_name,
						$threshold
					);
				}
			}
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with image size registration',
						'%d issues detected with image size registration',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-size-registration',
				'details'      => array(
					'issues'             => $issues,
					'registered_sizes'   => count( $registered_sizes ),
					'missing_size_count' => $missing_size_count,
					'missing_sizes'      => $missing_sizes,
					'orphaned_sizes'     => array_keys( $orphaned_patterns ?? array() ),
					'max_dimensions'     => $max_width . ' x ' . $max_height,
				),
			);
		}

		return null;
	}
}
