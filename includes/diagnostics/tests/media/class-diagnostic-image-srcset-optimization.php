<?php
/**
 * Image Srcset Optimization Diagnostic
 *
 * Tests if images are optimized with srcset for responsive display on different devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1010
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Srcset Optimization Diagnostic Class
 *
 * Validates that images are served with srcset attributes for responsive
 * images, allowing browsers to select appropriate size for device/viewport.
 *
 * @since 1.7034.1010
 */
class Diagnostic_Image_Srcset_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-srcset-optimization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Srcset Optimization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates images are served with srcset for responsive display';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if featured images and content images are generated with
	 * multiple sizes for responsive image srcset attribute.
	 *
	 * @since  1.7034.1010
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Get image sizes registered in WordPress.
		global $_wp_additional_image_sizes;
		$default_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );
		$additional_sizes = (array) $_wp_additional_image_sizes;
		$total_sizes = count( $default_sizes ) + count( $additional_sizes );

		// Get image size configuration.
		$thumbnail_size_w = absint( get_option( 'thumbnail_size_w' ) );
		$medium_size_w    = absint( get_option( 'medium_size_w' ) );
		$large_size_w     = absint( get_option( 'large_size_w' ) );

		// Check if multiple sizes are actually different.
		$sizes_are_unique = ( $thumbnail_size_w !== $medium_size_w ) &&
						   ( $medium_size_w !== $large_size_w );

		// Check for responsive image plugin.
		$has_responsive_plugin = is_plugin_active( 'wp-smushit/wp-smush.php' ) ||
								is_plugin_active( 'responsive-images/responsive-images.php' );

		// Test featured image srcset.
		$test_post = get_page_by_title( 'Sample Page', OBJECT, 'page' );
		$featured_image_id = $test_post ? get_post_thumbnail_id( $test_post->ID ) : 0;
		$has_featured_srcset = false;

		if ( $featured_image_id ) {
			$srcset = wp_get_attachment_image_srcset( $featured_image_id );
			$has_featured_srcset = ! empty( $srcset );
		}

		// Get recent posts with featured images.
		global $wpdb;
		$posts_with_featured = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = 'post'
			 AND p.post_status = 'publish'
			 AND pm.meta_key = '_thumbnail_id'
			 AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		// Sample content images to check for srcset.
		$recent_posts = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type = 'post' AND post_status = 'publish'
			 AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
			 ORDER BY post_date DESC LIMIT 5",
			ARRAY_A
		);

		$posts_with_srcset = 0;
		$posts_checked = 0;

		foreach ( $recent_posts as $post ) {
			$content = get_post_field( 'post_content', $post['ID'] );
			if ( strpos( $content, 'srcset=' ) !== false ) {
				$posts_with_srcset++;
			}
			if ( strpos( $content, '<img' ) !== false ) {
				$posts_checked++;
			}
		}

		// Check theme support for responsive images.
		$theme_supports_responsive = current_theme_supports( 'html5', 'style' ) || 
									 current_theme_supports( 'custom-header' );

		// Check image generate sizes setting.
		$intermediate_image_sizes = get_intermediate_image_sizes();
		$gen_sizes_count = count( $intermediate_image_sizes );

		// Check for issues.
		$issues = array();

		// Issue 1: Very few image sizes registered.
		if ( $total_sizes < 3 ) {
			$issues[] = array(
				'type'        => 'few_sizes',
				'description' => sprintf(
					/* translators: %d: number of image sizes */
					__( 'Only %d image size(s) registered; should have at least 3-4 for responsive images', 'wpshadow' ),
					$total_sizes
				),
			);
		}

		// Issue 2: Image sizes not unique.
		if ( ! $sizes_are_unique ) {
			$issues[] = array(
				'type'        => 'duplicate_sizes',
				'description' => __( 'Multiple registered image sizes have identical dimensions; redundant for srcset', 'wpshadow' ),
			);
		}

		// Issue 3: Featured image doesn't have srcset.
		if ( $featured_image_id && ! $has_featured_srcset ) {
			$issues[] = array(
				'type'        => 'no_featured_srcset',
				'description' => __( 'Featured images are not served with srcset attribute for responsive display', 'wpshadow' ),
			);
		}

		// Issue 4: Content images missing srcset.
		if ( $posts_checked > 0 && $posts_with_srcset < ( $posts_checked * 0.5 ) ) {
			$issues[] = array(
				'type'        => 'low_srcset_coverage',
				'description' => sprintf(
					/* translators: %d: percentage of posts with srcset */
					__( 'Only %d%% of recent posts have srcset on images; should be 100%%', 'wpshadow' ),
					round( ( $posts_with_srcset / $posts_checked ) * 100 )
				),
			);
		}

		// Issue 5: No intermediate image generation.
		if ( $gen_sizes_count < 3 ) {
			$issues[] = array(
				'type'        => 'limited_generation',
				'description' => __( 'Limited intermediate image sizes generated for srcset; upload smaller size variations', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Images are not optimized with srcset for responsive display on different devices and screen sizes', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-srcset-optimization',
				'details'      => array(
					'total_image_sizes'        => $total_sizes,
					'default_sizes'            => $default_sizes,
					'additional_sizes'         => $additional_sizes,
					'sizes_are_unique'         => $sizes_are_unique,
					'thumbnail_width'          => $thumbnail_size_w,
					'medium_width'             => $medium_size_w,
					'large_width'              => $large_size_w,
					'has_responsive_plugin'    => $has_responsive_plugin,
					'featured_image_srcset'    => $has_featured_srcset,
					'posts_with_featured'      => absint( $posts_with_featured ),
					'posts_checked'            => $posts_checked,
					'posts_with_srcset'        => $posts_with_srcset,
					'intermediate_sizes'       => $gen_sizes_count,
					'theme_responsive_support' => $theme_supports_responsive,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Register multiple image sizes (3-5) and ensure theme generates responsive images', 'wpshadow' ),
					'performance_improvement'  => '30-50% bandwidth savings on mobile devices',
					'image_size_recommendations' => array(
						'thumbnail'    => '150x150 (small thumbnails)',
						'medium'       => '300x300 (medium displays)',
						'medium_large' => '768x0 (tablets)',
						'large'        => '1024x0 (desktop)',
						'xlarge'       => '1536x0 (large displays)',
					),
				),
			);
		}

		return null;
	}
}
