<?php
/**
 * No Alt Text for Images Diagnostic
 *
 * Detects when images lack alt text,
 * making them inaccessible and hurting SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Alt Text for Images
 *
 * Checks whether images have descriptive
 * alt text for accessibility and SEO.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Alt_Text_For_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-alt-text-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Alt Text for Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether images have alt text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for images without alt text
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Count total images
		preg_match_all( '/<img[^>]*>/i', $body, $all_images );
		$total_images = count( $all_images[0] );

		if ( $total_images === 0 ) {
			return null;
		}

		// Count images with alt text
		preg_match_all( '/<img[^>]*alt=["\'][^"\']+["\'][^>]*>/i', $body, $images_with_alt );
		$images_with_alt_count = count( $images_with_alt[0] );

		$percentage_with_alt = ( $images_with_alt_count / $total_images ) * 100;

		if ( $percentage_with_alt < 80 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'About %d%% of your images lack alt text, which makes them invisible to screen readers and search engines. Alt text: describes images for blind users ("photo of golden retriever playing fetch"), helps if images fail to load, improves image search SEO. Good alt text is descriptive but concise. Decorative images should have alt="" (empty, not missing). This is required for WCAG AA compliance and helps SEO.',
						'wpshadow'
					),
					round( 100 - $percentage_with_alt )
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'total_images'  => $total_images,
				'images_with_alt' => $images_with_alt_count,
				'percentage_without_alt' => 100 - $percentage_with_alt,
				'business_impact' => array(
					'metric'         => 'Accessibility & Image SEO',
					'potential_gain' => 'Enable blind users to understand images, improve image search',
					'roi_explanation' => 'Alt text makes images accessible to 2% of users who are blind and improves image search rankings.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/alt-text-for-images',
			);
		}

		return null;
	}
}
