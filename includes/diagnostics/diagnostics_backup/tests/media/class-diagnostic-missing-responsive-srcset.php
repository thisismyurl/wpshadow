<?php
/**
 * Missing Responsive Image Srcset Diagnostic
 *
 * Detects images missing srcset attribute for multiple resolutions,
 * causing oversized mobile image delivery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6028.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Responsive Srcset Diagnostic Class
 *
 * Checks if images are using srcset for responsive delivery,
 * crucial for mobile performance optimization.
 *
 * @since 1.6028.1430
 */
class Diagnostic_Missing_Responsive_Srcset extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-responsive-srcset';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Responsive Image Srcset';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects images missing srcset attribute for responsive delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-responsive';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$srcset_analysis = self::analyze_srcset_usage();

		if ( $srcset_analysis['percentage'] < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage of images with srcset */
					__( 'Only %d%% of images have responsive srcset attributes', 'wpshadow' ),
					$srcset_analysis['percentage']
				),
				'severity'     => $srcset_analysis['percentage'] < 30 ? 'medium' : 'low',
				'threat_level' => 50 - $srcset_analysis['percentage'],
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/responsive-images-srcset',
				'meta'         => array(
					'total_images'      => $srcset_analysis['total'],
					'with_srcset'       => $srcset_analysis['with_srcset'],
					'without_srcset'    => $srcset_analysis['without_srcset'],
					'percentage'        => $srcset_analysis['percentage'],
					'wp_version'        => get_bloginfo( 'version' ),
					'image_sizes'       => $srcset_analysis['image_sizes'],
				),
				'details'      => array(
					'finding'        => sprintf(
						/* translators: 1: images without srcset, 2: total images */
						__( '%1$d of %2$d images lack responsive srcset attributes', 'wpshadow' ),
						$srcset_analysis['without_srcset'],
						$srcset_analysis['total']
					),
					'impact'         => __( 'Mobile users download full-size desktop images, wasting data and slowing page loads. Srcset allows browsers to request appropriately-sized images based on device capabilities.', 'wpshadow' ),
					'recommendation' => __( 'Regenerate thumbnails and ensure theme properly implements wp_get_attachment_image()', 'wpshadow' ),
					'solution_free'  => array(
						'label' => __( 'Regenerate Thumbnails', 'wpshadow' ),
						'steps' => array(
							__( '1. Install "Regenerate Thumbnails" plugin', 'wpshadow' ),
							__( '2. Go to Tools → Regen. Thumbnails', 'wpshadow' ),
							__( '3. Click "Regenerate All Thumbnails"', 'wpshadow' ),
							__( '4. Ensure theme uses the_post_thumbnail()', 'wpshadow' ),
						),
					),
					'solution_premium' => array(
						'label' => __( 'Image CDN with Auto-Srcset', 'wpshadow' ),
						'steps' => array(
							__( '1. Enable Cloudflare Images or Jetpack CDN', 'wpshadow' ),
							__( '2. Configure auto-resize options', 'wpshadow' ),
							__( '3. Enable srcset generation', 'wpshadow' ),
							__( '4. Test on multiple devices', 'wpshadow' ),
						),
					),
					'solution_advanced' => array(
						'label' => __( 'Custom Srcset Implementation', 'wpshadow' ),
						'steps' => array(
							__( '1. Add custom image sizes in functions.php', 'wpshadow' ),
							__( '2. Use wp_calculate_image_srcset()', 'wpshadow' ),
							__( '3. Implement <picture> element for art direction', 'wpshadow' ),
							__( '4. Add sizes attribute for layout hints', 'wpshadow' ),
						),
					),
					'test_steps'     => array(
						__( '1. Use Chrome DevTools mobile emulation', 'wpshadow' ),
						__( '2. Check Network tab for image sizes', 'wpshadow' ),
						__( '3. Verify smaller images on mobile', 'wpshadow' ),
						__( '4. Test with Lighthouse audit', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}

	/**
	 * Analyze srcset usage across images.
	 *
	 * @since  1.6028.1430
	 * @return array Srcset analysis data.
	 */
	private static function analyze_srcset_usage() {
		global $wpdb;

		// Check recent posts for image analysis.
		$posts = $wpdb->get_results(
			"SELECT ID, post_content 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_status = 'publish' 
			ORDER BY post_modified DESC 
			LIMIT 20"
		);

		$total_images    = 0;
		$with_srcset     = 0;
		$without_srcset  = 0;

		foreach ( $posts as $post ) {
			// Find img tags.
			preg_match_all( '/<img[^>]+>/i', $post->post_content, $img_tags );
			
			if ( ! empty( $img_tags[0] ) ) {
				foreach ( $img_tags[0] as $img_tag ) {
					$total_images++;
					
					if ( strpos( $img_tag, 'srcset=' ) !== false ) {
						$with_srcset++;
					} else {
						$without_srcset++;
					}
				}
			}
		}

		// Get registered image sizes.
		$image_sizes = array();
		global $_wp_additional_image_sizes;
		foreach ( get_intermediate_image_sizes() as $size ) {
			if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$image_sizes[ $size ] = $_wp_additional_image_sizes[ $size ];
			}
		}

		$percentage = $total_images > 0 ? round( ( $with_srcset / $total_images ) * 100 ) : 100;

		return array(
			'total'          => $total_images,
			'with_srcset'    => $with_srcset,
			'without_srcset' => $without_srcset,
			'percentage'     => $percentage,
			'image_sizes'    => count( $image_sizes ),
		);
	}
}
