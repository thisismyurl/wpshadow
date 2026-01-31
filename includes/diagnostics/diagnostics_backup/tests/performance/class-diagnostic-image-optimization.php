<?php
/**
 * Image Optimization Diagnostic
 *
 * Analyzes images on the site to identify unoptimized images that could be
 * compressed for faster loading.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Optimization Class
 *
 * Detects unoptimized images causing performance degradation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies unoptimized images degrading performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if optimization opportunities detected, null otherwise.
	 */
	public static function check() {
		$image_analysis = self::analyze_images();

		if ( ! $image_analysis || $image_analysis['unoptimized_count'] === 0 ) {
			return null;
		}

		$severity = ( $image_analysis['total_unoptimized_size'] > 50 ) ? 'high' : 'medium';
		$threat_level = ( $severity === 'high' ) ? 70 : 50;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: MB size, %d: image count */
				__( 'Found %d unoptimized images using %.1f MB. Compression could save %.1f MB.', 'wpshadow' ),
				$image_analysis['unoptimized_count'],
				$image_analysis['total_unoptimized_size'],
				$image_analysis['potential_savings']
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/optimize-images',
			'family'        => self::$family,
			'meta'          => array(
				'unoptimized_image_count'  => $image_analysis['unoptimized_count'],
				'total_unoptimized_mb'     => number_format( $image_analysis['total_unoptimized_size'], 1 ),
				'potential_savings_mb'     => number_format( $image_analysis['potential_savings'], 1 ),
				'compression_percentage'   => $image_analysis['compression_percentage'] . '%',
				'page_load_impact'         => sprintf(
					__( 'Could reduce page load time by 5-15%% (~%.1f seconds)' ),
					$image_analysis['estimated_speed_improvement']
				),
			),
			'details'       => array(
				'issue'        => __( 'Large, uncompressed images are the #1 cause of slow page loads. Compression reduces file size without visible quality loss.' ),
				'optimization_tools' => array(
					'Automatic (Recommended)' => array(
						'Imagify' => 'Free tier: 20MB/month, auto-compress on upload',
						'ShortPixel' => 'Free tier: 100 images/month, excellent quality',
						'Smush' => 'Built into WordPress, one-click compression',
						'Cloudflare Mirage' => 'CDN-based image optimization',
					),
					'Manual Tools' => array(
						'TinyPNG' => 'Best compression, bulk upload, free tier available',
						'ImageOptim' => 'Mac desktop app, lossless compression',
						'FileOptimizer' => 'Windows desktop app, multiple formats',
					),
				),
				'quick_setup_steps' => array(
					'Step 1' => __( 'Install image optimization plugin (Imagify or ShortPixel recommended)' ),
					'Step 2' => __( 'Configure: Enable automatic compression on upload' ),
					'Step 3' => __( 'Run optimization: Bulk optimize existing media library' ),
					'Step 4' => __( 'Enable WebP: Serve modern format to modern browsers' ),
					'Step 5' => __( 'Set up CDN: Deliver images from server closer to users' ),
					'Step 6' => __( 'Monitor: Check bandwidth savings in plugin dashboard' ),
				),
				'image_formats'    => array(
					'WebP' => 'Modern format, 25-35% smaller than JPEG, all browsers support now',
					'JPEG' => 'Best for photos, ~70-80% compression quality',
					'PNG' => 'Best for graphics/logos, smaller with optimization',
					'AVIF' => 'Newest format, 50-60% smaller than JPEG, limited support',
				),
				'lazy_loading'     => array(
					__( 'Also implement lazy loading: Images load only when scrolling into view' ),
					__( 'Reduces initial page load time significantly' ),
					__( 'Most modern WP plugins include lazy loading' ),
					__( 'Enable in theme settings or with plugin' ),
				),
			),
		);
	}

	/**
	 * Analyze images in media library.
	 *
	 * @since  1.2601.2148
	 * @return array|null Image analysis data.
	 */
	private static function analyze_images() {
		global $wpdb;

		// Get attachment count and estimated size
		$attachments = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image%' LIMIT 50"
		);

		if ( empty( $attachments ) ) {
			return null;
		}

		$total_size = 0;
		$unoptimized_count = 0;

		foreach ( $attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );
			if ( file_exists( $file_path ) ) {
				$file_size = filesize( $file_path ) / ( 1024 * 1024 ); // Convert to MB

				// Estimate optimization savings (typically 30-50%)
				if ( $file_size > 1.0 ) { // Only count images > 1MB
					$unoptimized_count ++;
					$total_size += $file_size;
				}
			}
		}

		if ( $unoptimized_count === 0 ) {
			return null;
		}

		$potential_savings = $total_size * 0.40; // Estimate 40% compression

		return array(
			'unoptimized_count'      => $unoptimized_count,
			'total_unoptimized_size' => $total_size,
			'potential_savings'      => $potential_savings,
			'compression_percentage' => 40,
			'estimated_speed_improvement' => $potential_savings / 10, // 0.1s per MB saved
		);
	}
}
