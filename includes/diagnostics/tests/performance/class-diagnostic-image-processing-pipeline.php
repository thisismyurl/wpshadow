<?php
/**
 * Image Processing Pipeline Diagnostic
 *
 * Analyzes image processing workflow and optimization pipeline.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Processing Pipeline Diagnostic
 *
 * Evaluates image processing and optimization workflow.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Processing_Pipeline extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-processing-pipeline';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Processing Pipeline';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes image processing workflow and optimization pipeline';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image optimization plugins
		$optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'shortpixel-image-optimiser/wp-shortpixel.php'  => 'ShortPixel',
			'imagify/imagify.php'                           => 'Imagify',
			'smush-free/wp-smush.php'                       => 'Smush',
			'wp-optimize/wp-optimize.php'                   => 'WP-Optimize',
		);

		$active_plugin = null;
		foreach ( $optimization_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		// Check GD/ImageMagick availability
		$has_gd          = extension_loaded( 'gd' );
		$has_imagemagick = extension_loaded( 'imagick' );

		// Check image processing library
		$image_library = 'none';
		if ( $has_imagemagick ) {
			$image_library = 'ImageMagick';
		} elseif ( $has_gd ) {
			$image_library = 'GD';
		}

		// Query total images
		global $wpdb;
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		// Generate findings if no optimization configured
		if ( ! $active_plugin && absint( $total_images ) > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of images */
					__( '%d images without automated optimization. Configure image processing pipeline for better performance.', 'wpshadow' ),
					absint( $total_images )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-processing-pipeline',
				'meta'         => array(
					'total_images'     => absint( $total_images ),
					'active_plugin'    => $active_plugin,
					'image_library'    => $image_library,
					'has_gd'           => $has_gd,
					'has_imagemagick'  => $has_imagemagick,
					'recommendation'   => 'Install ShortPixel, Imagify, or EWWW Image Optimizer',
					'impact_estimate'  => '30-60% image size reduction typical',
					'compression_types' => array(
						'Lossy: 70-90% quality, 50-70% size reduction',
						'Lossless: 100% quality, 10-30% size reduction',
						'Glossy: 90-95% quality, 40-60% size reduction',
					),
				),
			);
		}

		// Check if only GD is available (ImageMagick is superior)
		if ( $has_gd && ! $has_imagemagick && absint( $total_images ) > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Using GD library for image processing. ImageMagick provides better quality and performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-processing-pipeline',
				'meta'         => array(
					'current_library'  => 'GD',
					'recommended'      => 'ImageMagick',
					'total_images'     => absint( $total_images ),
					'recommendation'   => 'Install ImageMagick PHP extension',
					'benefits'         => array(
						'Better compression algorithms',
						'More format support (AVIF, WebP)',
						'Faster processing',
						'Better color management',
					),
				),
			);
		}

		return null;
	}
}
