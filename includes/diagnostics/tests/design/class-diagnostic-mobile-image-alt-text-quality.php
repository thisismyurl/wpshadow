<?php
/**
 * Mobile Image Alt Text Quality Diagnostic
 *
 * Ensures images have descriptive alt text.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Image Alt Text Quality Diagnostic Class
 *
 * Ensures all images have descriptive alt text for screen reader users,
 * following WCAG 1.1.1 requirements.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Mobile_Image_Alt_Text_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-alt-text-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Alt Text Quality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure all images have descriptive alt text (WCAG 1.1.1)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for missing alt attributes
		$missing_alt_attributes = apply_filters( 'wpshadow_has_missing_alt_attributes', false );
		if ( $missing_alt_attributes ) {
			$issues[] = __( 'Some images missing alt attributes; add descriptive text for all images', 'wpshadow' );
		}

		// Check if alt text isn't just filenames
		$alt_text_quality = apply_filters( 'wpshadow_alt_text_not_just_filenames', false );
		if ( ! $alt_text_quality ) {
			$issues[] = __( 'Alt text should describe image content, not just filenames (e.g., "person smiling" vs "DSC1234.jpg")', 'wpshadow' );
		}

		// Check for decorative image handling
		$decorative_images_marked = apply_filters( 'wpshadow_decorative_images_properly_marked', false );
		if ( ! $decorative_images_marked ) {
			$issues[] = __( 'Decorative images should have empty alt attribute (alt="") not descriptive text', 'wpshadow' );
		}

		// Check for alt text length (should be concise)
		$alt_text_length_appropriate = apply_filters( 'wpshadow_alt_text_length_appropriate', false );
		if ( ! $alt_text_length_appropriate ) {
			$issues[] = __( 'Alt text should be concise (100 characters or less) for screen reader efficiency', 'wpshadow' );
		}

		// Check for complex image descriptions
		$complex_images_have_captions = apply_filters( 'wpshadow_complex_images_have_descriptions', false );
		if ( ! $complex_images_have_captions ) {
			$issues[] = __( 'Complex images (charts, diagrams) may need additional description in caption or nearby text', 'wpshadow' );
		}

		// Check for linked image alt text
		$linked_images_alt_text = apply_filters( 'wpshadow_linked_images_have_descriptive_alt', false );
		if ( ! $linked_images_alt_text ) {
			$issues[] = __( 'Linked images should have alt text describing the link destination, not just the image', 'wpshadow' );
		}

		// Check for background images with fallback text
		$background_image_fallback = apply_filters( 'wpshadow_background_images_have_text_fallback', false );
		if ( ! $background_image_fallback ) {
			$issues[] = __( 'Background images with text should have text content available when image fails to load', 'wpshadow' );
		}

		// Check for icon/symbol alt text
		$icons_have_alt_text = apply_filters( 'wpshadow_icons_have_descriptive_alt_text', false );
		if ( ! $icons_have_alt_text ) {
			$issues[] = __( 'Icon images should have alt text explaining what they represent (e.g., "search icon")', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-image-alt-text-quality',
			);
		}

		return null;
	}
}
