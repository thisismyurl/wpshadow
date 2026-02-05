<?php
/**
 * Image Alt Text Generation Not Implemented Treatment
 *
 * Checks if auto alt text generation is implemented.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Alt Text Generation Not Implemented Treatment Class
 *
 * Detects missing auto alt text.
 *
 * @since 1.6030.2352
 */
class Treatment_Image_Alt_Text_Generation_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-text-generation-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text Generation Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if auto alt text generation is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Sample recent images to check for alt text.
		global $wpdb;
		
		$recent_images = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
			 WHERE post_type = 'attachment' 
			 AND post_mime_type LIKE 'image%'
			 AND post_status = 'inherit'
			 ORDER BY post_date DESC 
			 LIMIT 50"
		);

		$total_images = count( $recent_images );
		$images_with_alt = 0;
		$images_without_alt = 0;

		foreach ( $recent_images as $image ) {
			$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			
			if ( ! empty( $alt_text ) ) {
				$images_with_alt++;
			} else {
				$images_without_alt++;
			}
		}

		$alt_percentage = $total_images > 0 ? round( ( $images_with_alt / $total_images ) * 100 ) : 0;

		// Check for alt text plugins or AI services.
		$alt_plugins = array(
			'auto-image-attributes/auto-image-attributes.php' => 'Auto Image Attributes',
			'bialty/bialty.php'                              => 'Bialty',
			'image-attributes-pro/image-attributes-pro.php'  => 'Image Attributes Pro',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $alt_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Critical: Many images without alt text.
		if ( $alt_percentage < 50 && $total_images > 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number without alt, 2: total images, 3: percentage */
					__( 'Image alt text missing. %1$d of %2$d recent images (%3$d%%) lack alt text. Alt text is critical for accessibility (screen readers) and SEO (image search). Add descriptive alt text to all images.', 'wpshadow' ),
					$images_without_alt,
					$total_images,
					100 - $alt_percentage
				),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image-alt-text',
				'details'     => array(
					'total_images'        => $total_images,
					'images_with_alt'     => $images_with_alt,
					'images_without_alt'  => $images_without_alt,
					'alt_percentage'      => $alt_percentage,
					'plugin_detected'     => $plugin_detected,
					'recommendation'      => __( 'Add alt text manually in Media Library, or install Auto Image Attributes plugin for bulk editing. Use descriptive text (not "image123.jpg"). Describe what image shows, not "image of".', 'wpshadow' ),
					'accessibility_impact' => array(
						'screen_readers' => 'Blind users cannot understand images without alt text',
						'wcag_requirement' => 'WCAG 2.1 Level A requires alt text on all images',
						'legal_risk'     => 'ADA lawsuits common for inaccessible websites',
					),
					'seo_benefits'        => array(
						'image_search' => 'Google Images indexes alt text',
						'context'      => 'Helps search engines understand images',
						'rankings'     => 'Can improve overall page rankings',
					),
					'best_practices'      => array(
						'Be descriptive ("Golden retriever playing fetch" not "dog")',
						'Keep under 125 characters',
						'Don\'t start with "image of" or "picture of"',
						'Include relevant keywords naturally',
						'Decorative images: use empty alt=""',
					),
				),
			);
		}

		// Medium: Some alt text but could be better.
		if ( $alt_percentage >= 50 && $alt_percentage < 80 ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Image Alt Text Could Be Improved', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: percentage with alt text */
					__( '%d%% of images have alt text. Good progress, but aim for 100%% coverage for accessibility and SEO.', 'wpshadow' ),
					$alt_percentage
				),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image-alt-text',
				'details'     => array(
					'alt_percentage'    => $alt_percentage,
					'images_without_alt' => $images_without_alt,
					'recommendation'     => __( 'Review images without alt text in Media Library. Add descriptive alt text to remaining images.', 'wpshadow' ),
				),
			);
		}

		// No issues - good alt text coverage.
		return null;
	}
}
