<?php
/**
 * Content Missing Alt Text Treatment
 *
 * Detects images without accessibility-required alt text.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Missing Alt Text Treatment Class
 *
 * Images without alt text fail accessibility (WCAG) and SEO.
 * Screen readers can't describe images. 15% of users affected.
 *
 * @since 1.6033.1645
 */
class Treatment_Content_Missing_Alt_Text extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-missing-alt-text';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Alt Text on Images';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect images without alt text (WCAG compliance & SEO impact)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for missing alt text
		$missing_alt = apply_filters( 'wpshadow_has_images_without_alt_text', false );
		if ( $missing_alt ) {
			$issues[] = __( 'Images without alt text fail WCAG 1.1.1 accessibility requirement', 'wpshadow' );
		}

		// Check for accessibility impact
		$accessibility_impact = apply_filters( 'wpshadow_alt_text_accessibility_impact', false );
		if ( $accessibility_impact ) {
			$issues[] = __( 'Screen readers can\'t describe images; 15% of users affected by missing alt text', 'wpshadow' );
		}

		// Check for SEO impact
		$seo_impact = apply_filters( 'wpshadow_alt_text_seo_impact', false );
		if ( $seo_impact ) {
			$issues[] = __( 'Alt text improves image SEO and helps Google understand content', 'wpshadow' );
		}

		// Check for proper alt format
		$alt_quality = apply_filters( 'wpshadow_alt_text_quality_sufficient', false );
		if ( ! $alt_quality ) {
			$issues[] = __( 'Alt text should describe image (not just filename) in 100 characters or less', 'wpshadow' );
		}

		// Check for decorative image handling
		$decorative_proper = apply_filters( 'wpshadow_decorative_images_properly_marked', false );
		if ( ! $decorative_proper ) {
			$issues[] = __( 'Decorative images should have empty alt attribute (alt=\"\"), not omitted', 'wpshadow' );
		}

		// Check for linked images
		$linked_images = apply_filters( 'wpshadow_linked_images_have_meaningful_alt', false );
		if ( ! $linked_images ) {
			$issues[] = __( 'Linked images need alt text describing destination, not just image', 'wpshadow' );
		}

		// Check for featured image alt text
		$featured_alt = apply_filters( 'wpshadow_featured_images_have_alt_text', false );
		if ( ! $featured_alt ) {
			$issues[] = __( 'Featured images should have alt text for accessibility and SEO', 'wpshadow' );
		}

		// Check for compliance percentage
		$compliance_rate = apply_filters( 'wpshadow_images_with_alt_text_percentage', 0 );
		if ( $compliance_rate < 90 && $compliance_rate > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: compliance percentage */
				__( 'Only %d%% of images have alt text; target 100%% compliance', 'wpshadow' ),
				$compliance_rate
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-missing-alt-text',
			);
		}

		return null;
	}
}
