<?php
/**
 * Media Image Optimization Integration Diagnostic
 *
 * Tests for image optimization plugin integration and validates
 * compression settings for optimal file sizes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1545
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Image_Optimization_Integration Class
 *
 * Ensures image optimization plugins are configured properly
 * and images are being compressed efficiently.
 *
 * @since 1.6033.1545
 */
class Diagnostic_Media_Image_Optimization_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-optimization-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests image optimization plugin configuration and compression';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1545
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for popular optimization plugins.
		$optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php'    => 'EWWW Image Optimizer',
			'imagify/imagify.php'                              => 'Imagify',
			'shortpixel-image-optimiser/wp-shortpixel.php'     => 'ShortPixel',
			'wp-smushit/wp-smush.php'                          => 'Smush',
			'optimus/optimus.php'                              => 'Optimus',
			'compress-jpeg-png-images/tiny-compress-images.php' => 'TinyPNG',
		);

		$active_optimizers = array();
		foreach ( $optimization_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_optimizers[] = $plugin_name;
			}
		}

		if ( empty( $active_optimizers ) ) {
			$issues[] = __( 'No image optimization plugin detected; images may be unnecessarily large', 'wpshadow' );
		}

		// Check JPEG quality setting.
		$jpeg_quality = apply_filters( 'jpeg_quality', 82, 'image_resize' );
		
		if ( $jpeg_quality > 90 ) {
			$issues[] = sprintf(
				/* translators: %d: JPEG quality percentage */
				__( 'JPEG quality is set to %d%%; consider reducing to 82%% for better file sizes', 'wpshadow' ),
				$jpeg_quality
			);
		} elseif ( $jpeg_quality < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: JPEG quality percentage */
				__( 'JPEG quality is set to %d%%; quality may be too low and cause visible artifacts', 'wpshadow' ),
				$jpeg_quality
			);
		}

		// Check WebP support.
		$webp_supported = false;
		if ( function_exists( 'imagewebp' ) ) {
			$webp_supported = true;
		}

		// Check if any active optimizer supports WebP.
		$webp_enabled = false;
		if ( is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			$webp_enabled = (bool) get_option( 'ewww_image_optimizer_webp' );
		} elseif ( is_plugin_active( 'imagify/imagify.php' ) ) {
			$imagify_options = get_option( 'imagify_settings' );
			$webp_enabled = ! empty( $imagify_options['convert_to_webp'] );
		} elseif ( is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) ) {
			$webp_enabled = (bool) get_option( 'wp-short-pixel-create-webp-markup' );
		} elseif ( is_plugin_active( 'wp-smushit/wp-smush.php' ) ) {
			$smush_settings = get_option( 'wp-smush-settings' );
			$webp_enabled = ! empty( $smush_settings['webp_mod'] );
		}

		if ( $webp_supported && ! $webp_enabled && ! empty( $active_optimizers ) ) {
			$issues[] = __( 'WebP format is supported but not enabled; consider enabling for better compression', 'wpshadow' );
		}

		// Check for lazy loading.
		$lazy_loading = wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' );
		
		if ( ! $lazy_loading ) {
			$issues[] = __( 'Lazy loading is disabled; enabling it can improve page load times', 'wpshadow' );
		}

		// Sample recent uploads to check if they're being optimized.
		$recent_uploads = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'posts_per_page' => 3,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$unoptimized_count = 0;
		foreach ( $recent_uploads as $upload ) {
			$file_path = get_attached_file( $upload->ID );
			if ( file_exists( $file_path ) ) {
				$file_size = filesize( $file_path );
				$image_data = getimagesize( $file_path );
				
				if ( $image_data ) {
					$width = $image_data[0];
					$height = $image_data[1];
					$pixels = $width * $height;
					
					// Rough estimate: JPEG should be ~0.5-1 byte per pixel when optimized.
					// If ratio is much higher, likely unoptimized.
					$bytes_per_pixel = $file_size / $pixels;
					
					if ( $bytes_per_pixel > 1.5 ) {
						$unoptimized_count++;
					}
				}
			}
		}

		if ( $unoptimized_count > 0 && ! empty( $recent_uploads ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent upload appears unoptimized; check optimization plugin configuration',
					'%d recent uploads appear unoptimized; check optimization plugin configuration',
					$unoptimized_count,
					'wpshadow'
				),
				$unoptimized_count
			);
		}

		// Check if optimization is running on upload.
		$has_optimization_hook = false;
		if ( has_filter( 'wp_generate_attachment_metadata' ) ) {
			$has_optimization_hook = true;
		}

		if ( ! empty( $active_optimizers ) && ! $has_optimization_hook ) {
			$issues[] = __( 'Optimization plugin is active but may not be processing uploads automatically', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-image-optimization',
			);
		}

		return null;
	}
}
