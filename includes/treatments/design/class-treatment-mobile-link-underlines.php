<?php
/**
 * Mobile Link Underlines Treatment
 *
 * Ensures links are visually distinguishable on mobile.
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
 * Mobile Link Underlines Treatment Class
 *
 * Ensures links are visually distinguishable from body text through underlines
 * or color differentiation, following WCAG 1.4.1.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Link_Underlines extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-link-underlines';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Link Underlines';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure links are visually distinguishable from body text (WCAG 1.4.1)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if links have text-decoration (underline)
		$links_underlined = apply_filters( 'wpshadow_links_have_underline_decoration', false );
		if ( ! $links_underlined ) {
			$issues[] = __( 'Links should be underlined (text-decoration: underline) to distinguish from body text', 'wpshadow' );
		}

		// Check if links have distinct color
		$links_color_distinct = apply_filters( 'wpshadow_link_color_distinct_from_body_text', false );
		if ( ! $links_color_distinct ) {
			$issues[] = __( 'Links should use distinct color differentiated from body text (WCAG 1.4.1)', 'wpshadow' );
		}

		// Check for link hover state visibility
		$link_hover_visible = apply_filters( 'wpshadow_link_hover_state_visually_distinct', false );
		if ( ! $link_hover_visible ) {
			$issues[] = __( 'Links should have visible hover state (color change, underline, etc)', 'wpshadow' );
		}

		// Check for link focus state visibility
		$link_focus_visible = apply_filters( 'wpshadow_link_focus_state_visually_distinct', false );
		if ( ! $link_focus_visible ) {
			$issues[] = __( 'Links should have visible focus state for keyboard navigation', 'wpshadow' );
		}

		// Check that color alone isn't the only differentiator
		$color_plus_other = apply_filters( 'wpshadow_links_use_color_plus_underline', false );
		if ( ! $color_plus_other ) {
			$issues[] = __( 'Use color AND underline/other visual cue (not color alone) per WCAG 1.4.1', 'wpshadow' );
		}

		// Check for sufficient color contrast between link and text
		$link_contrast_adequate = apply_filters( 'wpshadow_link_color_contrast_adequate', false );
		if ( ! $link_contrast_adequate ) {
			$issues[] = __( 'Link color should have sufficient contrast from body text (min 3:1 ratio)', 'wpshadow' );
		}

		// Check for visited link styling
		$visited_links_styled = apply_filters( 'wpshadow_visited_links_visually_distinct', false );
		if ( ! $visited_links_styled ) {
			$issues[] = __( 'Visited links should be visually distinct (usually via color change)', 'wpshadow' );
		}

		// Check that link underlines aren't removed on hover
		$underlines_persist = apply_filters( 'wpshadow_link_underlines_persist_on_hover', false );
		if ( ! $underlines_persist ) {
			$issues[] = __( 'Link underlines should persist on hover (not disappear) for clarity', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-link-underlines',
			);
		}

		return null;
	}
}
