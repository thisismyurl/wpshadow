<?php
/**
 * Mobile SVG Accessibility Treatment
 *
 * Ensures SVG icons are accessible to screen readers.
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
 * Mobile SVG Accessibility Treatment Class
 *
 * Ensures SVG icons have proper accessibility features including title/desc elements
 * and ARIA roles for screen reader users.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_SVG_Accessibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-svg-accessibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile SVG Accessibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure SVG icons have proper accessibility labels and ARIA roles';

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

		// Check for SVG elements in markup
		$has_svg_elements = apply_filters( 'wpshadow_page_has_svg_elements', false );
		if ( ! $has_svg_elements ) {
			return null; // No SVG to check
		}

		// Check if SVGs have title elements
		$svg_has_titles = apply_filters( 'wpshadow_svg_elements_have_title', false );
		if ( ! $svg_has_titles ) {
			$issues[] = __( 'SVG icons should include <title> elements for screen reader descriptions', 'wpshadow' );
		}

		// Check if SVGs have description elements
		$svg_has_descriptions = apply_filters( 'wpshadow_svg_elements_have_description', false );
		if ( ! $svg_has_descriptions ) {
			$issues[] = __( 'SVG icons may need <desc> elements for detailed descriptions', 'wpshadow' );
		}

		// Check for ARIA labels on SVGs
		$svg_aria_labeled = apply_filters( 'wpshadow_svg_elements_aria_labeled', false );
		if ( ! $svg_aria_labeled ) {
			$issues[] = __( 'SVG icons should have aria-label or aria-labelledby for accessibility', 'wpshadow' );
		}

		// Check if SVGs have proper ARIA roles
		$svg_aria_roles = apply_filters( 'wpshadow_svg_elements_have_aria_roles', false );
		if ( ! $svg_aria_roles ) {
			$issues[] = __( 'SVG icons should declare ARIA roles (img, presentation, etc)', 'wpshadow' );
		}

		// Check for decorative SVG handling
		$decorative_svg_marked = apply_filters( 'wpshadow_decorative_svg_properly_marked', false );
		if ( ! $decorative_svg_marked ) {
			$issues[] = __( 'Decorative SVGs should have role="presentation" and empty alt attribute', 'wpshadow' );
		}

		// Check for SVG width/height attributes
		$svg_dimensions_set = apply_filters( 'wpshadow_svg_elements_have_dimensions', false );
		if ( ! $svg_dimensions_set ) {
			$issues[] = __( 'SVG elements should include viewBox and width/height attributes', 'wpshadow' );
		}

		// Check if SVG doesn't block other content
		$svg_positioning = apply_filters( 'wpshadow_svg_positioning_valid', false );
		if ( ! $svg_positioning ) {
			$issues[] = __( 'SVG icons should not block or overlap page content', 'wpshadow' );
		}

		// Check for focus-able SVG interactive elements
		$svg_focusable_interactive = apply_filters( 'wpshadow_svg_interactive_elements_focusable', false );
		if ( ! $svg_focusable_interactive ) {
			$issues[] = __( 'Interactive SVG elements should be keyboard accessible and focusable', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-svg-accessibility',
			);
		}

		return null;
	}
}
