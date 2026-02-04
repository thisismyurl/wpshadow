<?php
/**
 * Responsive Image Srcset Generation Diagnostic
 *
 * Validates srcset attribute generation for responsive images.
 * Tests multiple size variants and browser selection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsive Image Srcset Generation Diagnostic Class
 *
 * Checks if WordPress is properly generating srcset attributes
 * for responsive images to serve optimal sizes per device.
 *
 * @since 1.7029.1200
 */
class Diagnostic_Responsive_Image_Srcset_Generation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'responsive-image-srcset-generation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Responsive Image Srcset Generation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates srcset attribute generation for responsive images';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress generates proper srcset attributes with
	 * multiple image size variants.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_version;

		// Srcset support added in WordPress 4.4.
		$wp_supports_srcset = version_compare( $wp_version, '4.4', '>=' );

		if ( ! $wp_supports_srcset ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress version is too old to support responsive image srcset attributes', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/responsive-image-srcset-generation',
				'details'      => array(
					'wp_version'        => $wp_version,
					'required_version'  => '4.4',
					'recommendation'    => __( 'Update WordPress to 4.4 or later for responsive image support', 'wpshadow' ),
				),
			);
		}

		// Get a test image.
		$test_image = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $test_image ) ) {
			return null; // No images to test.
		}

		$attachment_id = $test_image[0]->ID;

		// Get image metadata.
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( empty( $metadata ) ) {
			return null;
		}

		// Check if image has multiple sizes generated.
		$available_sizes = isset( $metadata['sizes'] ) ? $metadata['sizes'] : array();
		$size_count      = count( $available_sizes );

		// Get registered image sizes.
		$registered_sizes = wp_get_registered_image_subsizes();
		$registered_count = count( $registered_sizes );

		// Generate srcset for test image.
		$srcset = wp_get_attachment_image_srcset( $attachment_id, 'large' );
		$sizes  = wp_get_attachment_image_sizes( $attachment_id, 'large' );

		// Count srcset entries.
		$srcset_count = 0;
		if ( $srcset ) {
			$srcset_array = explode( ',', $srcset );
			$srcset_count = count( $srcset_array );
		}

		// Test with wp_get_attachment_image().
		$img_html = wp_get_attachment_image( $attachment_id, 'large' );

		// Check if HTML contains srcset.
		$has_srcset_in_html = false !== strpos( $img_html, 'srcset' );
		$has_sizes_in_html  = false !== strpos( $img_html, 'sizes' );

		// Issue: srcset not being generated or insufficient sizes.
		if ( ! $srcset || 2 > $srcset_count || ! $has_srcset_in_html ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Responsive image srcset attributes are not being generated properly, forcing all devices to load full-size images', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/responsive-image-srcset-generation',
				'details'      => array(
					'tested_image'       => array(
						'id'       => $attachment_id,
						'title'    => get_the_title( $attachment_id ),
						'url'      => wp_get_attachment_url( $attachment_id ),
					),
					'wp_version'         => $wp_version,
					'wp_supports_srcset' => $wp_supports_srcset,
					'srcset_generated'   => ! empty( $srcset ),
					'srcset_count'       => $srcset_count,
					'available_sizes'    => $size_count,
					'registered_sizes'   => $registered_count,
					'has_srcset_in_html' => $has_srcset_in_html,
					'has_sizes_in_html'  => $has_sizes_in_html,
					'sample_srcset'      => $srcset ? $srcset : __( 'None generated', 'wpshadow' ),
					'sample_sizes'       => $sizes ? $sizes : __( 'None generated', 'wpshadow' ),
					'performance_impact' => __( 'Without srcset, mobile devices load full-resolution images, wasting bandwidth and slowing load times', 'wpshadow' ),
					'recommendation'     => __( 'Ensure theme uses wp_get_attachment_image() or the_post_thumbnail() instead of hardcoded img tags', 'wpshadow' ),
					'possible_causes'    => array(
						2 > $size_count ? __( 'Not enough image sizes registered (need at least 2-3 variations)', 'wpshadow' ) : '',
						! $has_srcset_in_html ? __( 'Theme is not using WordPress image functions (wp_get_attachment_image, the_post_thumbnail)', 'wpshadow' ) : '',
						empty( $srcset ) ? __( 'Image metadata missing or corrupted - try regenerating thumbnails', 'wpshadow' ) : '',
					),
					'fix_steps'          => array(
						__( '1. Install "Regenerate Thumbnails" plugin', 'wpshadow' ),
						__( '2. Run thumbnail regeneration for all images', 'wpshadow' ),
						__( '3. Check theme uses wp_get_attachment_image() not <img src="">', 'wpshadow' ),
						__( '4. Verify Settings → Media has multiple image sizes defined', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
