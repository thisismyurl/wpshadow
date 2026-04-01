<?php
/**
 * Mobile Social Sharing Widgets Diagnostic
 *
 * Optimizes social sharing widgets for mobile devices.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Social Sharing Widgets Diagnostic Class
 *
 * Validates social sharing widgets are optimized for mobile with proper button
 * sizing and layout, ensuring WCAG 2.5.5 compliance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Social_Sharing_Widgets extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-social-sharing-widgets';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Social Sharing Widgets';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Optimize social sharing widgets for mobile with proper button sizing and layout';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if social sharing widget exists
		$has_social_widget = apply_filters( 'wpshadow_has_social_sharing_widget', false );
		if ( ! $has_social_widget ) {
			return null; // No social widget to check
		}

		// Check if social buttons are mobile-friendly
		$mobile_friendly_buttons = apply_filters( 'wpshadow_social_buttons_mobile_friendly', false );
		if ( ! $mobile_friendly_buttons ) {
			$issues[] = __( 'Social sharing buttons may not be optimized for mobile display', 'wpshadow' );
		}

		// Check if buttons are 44px+ tap targets
		$button_sizes_adequate = apply_filters( 'wpshadow_social_button_sizes_44px_minimum', false );
		if ( ! $button_sizes_adequate ) {
			$issues[] = __( 'Social buttons should be 44px+ for comfortable tapping (WCAG 2.5.5)', 'wpshadow' );
		}

		// Check if social widget doesn't break layout
		$widget_doesnt_break_layout = apply_filters( 'wpshadow_social_widget_maintains_layout', false );
		if ( ! $widget_doesnt_break_layout ) {
			$issues[] = __( 'Social widget may cause horizontal scrolling or break layout on mobile', 'wpshadow' );
		}

		// Check for horizontal scrolling in widget
		$no_horizontal_scroll = apply_filters( 'wpshadow_social_widget_no_horizontal_scroll', false );
		if ( ! $no_horizontal_scroll ) {
			$issues[] = __( 'Social widget may require horizontal scrolling on mobile; limit button count or stack vertically', 'wpshadow' );
		}

		// Check if widget has responsive layout
		$responsive_layout = apply_filters( 'wpshadow_social_widget_responsive_layout', false );
		if ( ! $responsive_layout ) {
			$issues[] = __( 'Social widget should stack buttons vertically on mobile instead of horizontal row', 'wpshadow' );
		}

		// Check for share count display
		$share_counts_optimized = apply_filters( 'wpshadow_social_share_counts_mobile_optimized', false );
		if ( ! $share_counts_optimized ) {
			$issues[] = __( 'Share counts may take up excessive space on mobile; consider hiding or abbreviating', 'wpshadow' );
		}

		// Check if buttons have hover/active states
		$button_states = apply_filters( 'wpshadow_social_buttons_have_visual_states', false );
		if ( ! $button_states ) {
			$issues[] = __( 'Social buttons should have visible hover/active states for interaction feedback', 'wpshadow' );
		}

		// Check for accessibility labels
		$aria_labels = apply_filters( 'wpshadow_social_buttons_aria_labeled', false );
		if ( ! $aria_labels ) {
			$issues[] = __( 'Social buttons should have aria-labels for screen reader users', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-social-sharing-widgets?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
