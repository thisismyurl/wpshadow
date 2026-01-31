<?php
/**
 * WebP Adoption Below 50% Diagnostic
 *
 * Measures percentage of images in modern WebP format compared to
 * legacy formats. WebP provides 25-35% file size reduction.
 *
 * @since   1.6028.1540
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WebP_Adoption Class
 *
 * Checks the percentage of images using modern WebP format versus
 * legacy JPEG/PNG formats.
 *
 * @since 1.6028.1540
 */
class Diagnostic_WebP_Adoption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-adoption-low';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Adoption Below 50%';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures percentage of images in modern WebP format providing 25-35% file size reduction';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Calculates the percentage of WebP images versus total images
	 * to identify optimization opportunities.
	 *
	 * @since  1.6028.1540
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$image_stats = self::analyze_image_formats();

		if ( $image_stats['total'] < 20 ) {
			return null; // Too few images to analyze
		}

		$webp_percentage = $image_stats['total'] > 0 
			? round( ( $image_stats['webp'] / $image_stats['total'] ) * 100 )
			: 0;

		if ( $webp_percentage >= 50 ) {
			return null; // Good WebP adoption
		}

		$severity = $webp_percentage < 10 ? 'medium' : 'low';
		$threat_level = $webp_percentage < 10 ? 40 : 25;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: 1: WebP percentage, 2: legacy image count, 3: potential bandwidth savings */
				__( 'Only %1$d%% of your images use modern WebP format. You have %2$d legacy JPEG/PNG images that could be converted, saving approximately %3$s in bandwidth.', 'wpshadow' ),
				$webp_percentage,
				$image_stats['legacy'],
				size_format( $image_stats['estimated_savings'] )
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/webp-images',
			'family'        => self::$family,
			'meta'          => array(
				'webp_count'          => $image_stats['webp'],
				'legacy_count'        => $image_stats['legacy'],
				'total_count'         => $image_stats['total'],
				'webp_percentage'     => $webp_percentage,
				'estimated_savings'   => size_format( $image_stats['estimated_savings'] ),
				'impact_level'        => $webp_percentage < 10 ? __( 'Medium - Significant bandwidth savings available', 'wpshadow' ) : __( 'Low - Performance optimization opportunity', 'wpshadow' ),
				'immediate_actions'   => array(
					__( 'Install Imagify or ShortPixel for automatic WebP conversion', 'wpshadow' ),
					__( 'Or enable WebP in existing image optimization plugin', 'wpshadow' ),
					__( 'Bulk optimize existing images to WebP', 'wpshadow' ),
					__( 'Test browser compatibility (WebP has 96%+ support)', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'WebP is a modern image format that provides 25-35% smaller file sizes than JPEG/PNG with the same visual quality. Smaller images mean faster page loads, lower bandwidth costs, and better Core Web Vitals scores. WebP is now supported by 96%+ of browsers including Chrome, Firefox, Edge, and Safari 14+.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Page Speed: 25-35% faster image loading', 'wpshadow' ),
					__( 'Mobile Users: Lower data usage on cellular', 'wpshadow' ),
					__( 'Bandwidth: Significant hosting cost savings', 'wpshadow' ),
					__( 'Core Web Vitals: Improved LCP and overall performance', 'wpshadow' ),
				),
				'solution_options' => array(
					'Imagify (Recommended)' => array(
						'description' => __( 'Auto-convert and serve WebP with fallback', 'wpshadow' ),
						'time'        => __( '15 minutes + bulk optimization time', 'wpshadow' ),
						'cost'        => __( 'Free (limited) or $4.99+/month', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'imagify',
						'steps'       => array(
							__( 'Install and activate Imagify', 'wpshadow' ),
							__( 'Enable WebP conversion in settings', 'wpshadow' ),
							__( 'Run bulk optimization on existing images', 'wpshadow' ),
							__( 'New uploads automatically converted', 'wpshadow' ),
						),
					),
					'ShortPixel' => array(
						'description' => __( 'Compress and convert to WebP', 'wpshadow' ),
						'time'        => __( '15 minutes', 'wpshadow' ),
						'cost'        => __( 'Free (100/month) or $4.99+/month', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'shortpixel-image-optimiser',
					),
					'EWWW Image Optimizer' => array(
						'description' => __( 'Free WebP conversion', 'wpshadow' ),
						'time'        => __( '20 minutes', 'wpshadow' ),
						'cost'        => __( 'Free (local conversion)', 'wpshadow' ),
						'difficulty'  => __( 'Medium', 'wpshadow' ),
						'plugin'      => 'ewww-image-optimizer',
					),
				),
				'best_practices'   => array(
					__( 'Use plugins that serve WebP with JPEG/PNG fallback', 'wpshadow' ),
					__( 'Convert both new uploads and existing library images', 'wpshadow' ),
					__( 'Test on Safari and older browsers for compatibility', 'wpshadow' ),
					__( 'Monitor image quality - use 80-85% compression', 'wpshadow' ),
					__( 'Combine WebP with lazy loading for maximum benefit', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Install Imagify or ShortPixel', 'wpshadow' ),
					'Step 2' => __( 'Enable WebP conversion in plugin settings', 'wpshadow' ),
					'Step 3' => __( 'Run bulk optimization on media library', 'wpshadow' ),
					'Step 4' => __( 'Test: Right-click image > Inspect > Check src ends in .webp', 'wpshadow' ),
					'Step 5' => __( 'Run PageSpeed Insights - should see "Serve images in next-gen formats" resolved', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze image format distribution.
	 *
	 * @since  1.6028.1540
	 * @return array Image format statistics.
	 */
	private static function analyze_image_formats() {
		global $wpdb;

		$stats = array(
			'webp'               => 0,
			'legacy'             => 0,
			'total'              => 0,
			'estimated_savings'  => 0,
		);

		// Count WebP images
		$stats['webp'] = intval(
			$wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type = 'image/webp'"
			)
		);

		// Count legacy images (JPEG, PNG, GIF)
		$stats['legacy'] = intval(
			$wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type IN ('image/jpeg', 'image/jpg', 'image/png', 'image/gif')"
			)
		);

		$stats['total'] = $stats['webp'] + $stats['legacy'];

		// Estimate bandwidth savings (30% average savings from WebP)
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];
		
		// Get average legacy image size (rough estimate)
		$legacy_images = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type IN ('image/jpeg', 'image/jpg', 'image/png', 'image/gif')
				LIMIT %d",
				100
			)
		);

		$total_size = 0;
		$count = 0;

		foreach ( $legacy_images as $image_id ) {
			$file = get_attached_file( $image_id );
			if ( $file && file_exists( $file ) ) {
				$total_size += filesize( $file );
				$count++;
			}
		}

		if ( $count > 0 ) {
			$avg_size = $total_size / $count;
			$stats['estimated_savings'] = $stats['legacy'] * $avg_size * 0.30; // 30% savings
		}

		return $stats;
	}
}
