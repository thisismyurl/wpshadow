<?php
/**
 * Thumbnail Generation Failures Diagnostic
 *
 * Detects failed thumbnail generation for uploaded images. Tests GD/ImageMagick availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Thumbnail_Generation_Failures Class
 *
 * Validates thumbnail generation process. WordPress uses GD or ImageMagick
 * to generate thumbnails. Failures can occur due to memory limits, library
 * issues, or corrupted source images.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Thumbnail_Generation_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'thumbnail-generation-failures';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Generation Failures';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects failed thumbnail generation for uploaded images';

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
	 * - Image library availability (GD/ImageMagick)
	 * - Missing thumbnail files
	 * - Memory limits for image processing
	 * - Image format support
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check which image library is available.
		$has_gd          = extension_loaded( 'gd' );
		$has_imagick     = extension_loaded( 'imagick' );
		$has_imagemagick = class_exists( 'Imagick' );

		if ( ! $has_gd && ! $has_imagick && ! $has_imagemagick ) {
			$issues[] = __( 'No image library available (GD or ImageMagick) - thumbnails cannot be generated', 'wpshadow' );
		}

		// Get the active image editor.
		$image_editor = wp_get_image_editor( __FILE__ ); // Test with any file.
		if ( is_wp_error( $image_editor ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Image editor initialization failed: %s', 'wpshadow' ),
				$image_editor->get_error_message()
			);
		}

		// Check memory limit for image processing.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$min_memory   = 256 * 1024 * 1024; // 256MB recommended.

		if ( $memory_limit > 0 && $memory_limit < $min_memory ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit (%s) is low - may cause thumbnail generation failures for large images', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for images with missing thumbnails.
		global $wpdb;

		// Find images uploaded in last 30 days without thumbnail metadata.
		$missing_thumbnails = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				AND (pm.meta_value IS NULL OR pm.meta_value NOT LIKE %s)",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) ),
				'%sizes%'
			)
		);

		if ( $missing_thumbnails > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image missing thumbnail metadata (uploaded in last 30 days)',
					'%d images missing thumbnail metadata (uploaded in last 30 days)',
					$missing_thumbnails,
					'wpshadow'
				),
				$missing_thumbnails
			);
		}

		// Check for thumbnail generation errors in transients.
		$thumb_errors = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND (option_value LIKE %s OR option_value LIKE %s)",
				$wpdb->esc_like( '_transient_' ) . '%',
				'%thumbnail%',
				'%image%'
			)
		);

		if ( $thumb_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of errors */
				_n(
					'%d thumbnail generation error found in transients',
					'%d thumbnail generation errors found in transients',
					$thumb_errors,
					'wpshadow'
				),
				$thumb_errors
			);
		}

		// Check GD library version and capabilities.
		if ( $has_gd ) {
			$gd_info = gd_info();

			// Check for required image format support.
			$required_formats = array(
				'JPEG Support'    => 'jpeg',
				'PNG Support'     => 'png',
				'GIF Read Support' => 'gif',
			);

			foreach ( $required_formats as $format_key => $format_name ) {
				if ( empty( $gd_info[ $format_key ] ) ) {
					$issues[] = sprintf(
						/* translators: %s: format name */
						__( 'GD library missing %s support', 'wpshadow' ),
						$format_name
					);
				}
			}

			// Check GD version.
			if ( ! empty( $gd_info['GD Version'] ) ) {
				$gd_version = $gd_info['GD Version'];
				if ( version_compare( $gd_version, '2.0', '<' ) ) {
					$issues[] = sprintf(
						/* translators: %s: version number */
						__( 'GD library version (%s) is outdated - recommend 2.0+', 'wpshadow' ),
						$gd_version
					);
				}
			}
		}

		// Check ImageMagick version and capabilities.
		if ( $has_imagemagick ) {
			$imagick = new \Imagick();
			$version = $imagick->getVersion();

			if ( ! empty( $version['versionString'] ) ) {
				// Check for old versions.
				if ( preg_match( '/ImageMagick ([0-9.]+)/', $version['versionString'], $matches ) ) {
					$im_version = $matches[1];
					if ( version_compare( $im_version, '6.2.4', '<' ) ) {
						$issues[] = sprintf(
							/* translators: %s: version number */
							__( 'ImageMagick version (%s) is outdated - recommend 6.2.4+', 'wpshadow' ),
							$im_version
						);
					}
				}
			}

			// Check supported formats.
			$formats = $imagick->queryFormats();
			$required_im_formats = array( 'JPEG', 'PNG', 'GIF' );

			foreach ( $required_im_formats as $format ) {
				if ( ! in_array( $format, $formats, true ) ) {
					$issues[] = sprintf(
						/* translators: %s: format name */
						__( 'ImageMagick missing %s support', 'wpshadow' ),
						$format
					);
				}
			}
		}

		// Check upload directory permissions (affects thumbnail generation).
		$upload_dir = wp_upload_dir();
		if ( ! wp_is_writable( $upload_dir['path'] ) ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Upload directory not writable: %s', 'wpshadow' ),
				$upload_dir['path']
			);
		}

		// Check for corrupt image files that fail thumbnail generation.
		$corrupt_images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.guid
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND pm.meta_key = '_wp_attachment_metadata'
				AND pm.meta_value LIKE %s
				AND p.post_date > %s
				LIMIT 10",
				'image/%',
				'%error%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		if ( ! empty( $corrupt_images ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of corrupt images */
				_n(
					'%d corrupt image file detected (metadata contains errors)',
					'%d corrupt image files detected (metadata contains errors)',
					count( $corrupt_images ),
					'wpshadow'
				),
				count( $corrupt_images )
			);
		}

		// Check max_execution_time for large image processing.
		$max_execution = (int) ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'max_execution_time (%d seconds) is low - large images may timeout during thumbnail generation', 'wpshadow' ),
				$max_execution
			);
		}

		// Check for disabled PHP functions that affect image processing.
		$disabled_functions = explode( ',', ini_get( 'disable_functions' ) );
		$disabled_functions = array_map( 'trim', $disabled_functions );

		$required_functions = array( 'getimagesize', 'imagecreatefromjpeg', 'imagecreatefrompng' );
		foreach ( $required_functions as $func ) {
			if ( in_array( $func, $disabled_functions, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: function name */
					__( 'Required PHP function disabled: %s', 'wpshadow' ),
					$func
				);
			}
		}

		// Check for plugins that might interfere with thumbnail generation.
		$active_plugins = get_option( 'active_plugins', array() );
		$known_conflicts = array(
			'ewww-image-optimizer'  => __( 'May delay or interfere with thumbnail generation', 'wpshadow' ),
			'imagify'               => __( 'May modify thumbnail generation process', 'wpshadow' ),
			'smush'                 => __( 'May alter thumbnail generation timing', 'wpshadow' ),
		);

		foreach ( $known_conflicts as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = sprintf(
						/* translators: 1: plugin slug, 2: conflict message */
						__( 'Plugin conflict (%1$s): %2$s', 'wpshadow' ),
						$plugin_slug,
						$message
					);
					break;
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
						'%d issue detected with thumbnail generation',
						'%d issues detected with thumbnail generation',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thumbnail-generation-failures?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'             => $issues,
					'has_gd'             => $has_gd,
					'has_imagick'        => $has_imagemagick,
					'memory_limit'       => size_format( $memory_limit ),
					'missing_thumbnails' => $missing_thumbnails,
					'thumb_errors'       => $thumb_errors,
					'corrupt_images'     => count( $corrupt_images ),
				),
			);
		}

		return null;
	}
}
