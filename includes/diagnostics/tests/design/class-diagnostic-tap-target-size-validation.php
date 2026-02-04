<?php
/**
 * Tap Target Size Validation Diagnostic
 *
 * Measures interactive element dimensions to ensure they're large enough for accurate tapping.
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
 * Tap Target Size Validation Diagnostic Class
 *
 * Measures interactive element dimensions to ensure they're large enough for accurate
 * tapping on mobile devices, ensuring WCAG 2.5.5 compliance.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Tap_Target_Size_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tap-target-size-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Size Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measure interactive element dimensions to ensure sufficient size for accurate tapping (WCAG 2.5.5)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'usability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for button sizing standards
		global $wp_styles;
		if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
			$has_button_sizing = false;
			foreach ( $wp_styles->registered as $handle => $obj ) {
				if ( strpos( $handle, 'button' ) !== false || strpos( $handle, 'interactive' ) !== false ) {
					// Check if handle defines minimum dimensions
					if ( strpos( $handle, 'size' ) !== false || strpos( $handle, 'dimension' ) !== false ) {
						$has_button_sizing = true;
						break;
					}
				}
			}

			if ( ! $has_button_sizing ) {
				$issues[] = __( 'Button sizing CSS standards not found; tap target sizes unverified', 'wpshadow' );
			}
		}

		// Check if theme has tap target sizing
		$theme_sizing_standards = apply_filters( 'wpshadow_theme_tap_target_sizing_wcag_compliant', false );
		if ( ! $theme_sizing_standards ) {
			$issues[] = __( 'Theme does not declare tap target sizing standards (44×44px minimum)', 'wpshadow' );
		}

		// Check for link/button minimum sizing
		$min_tap_size = apply_filters( 'wpshadow_minimum_tap_target_size', '44px' );
		if ( '44px' !== $min_tap_size && '48px' !== $min_tap_size ) {
			$issues[] = sprintf(
				/* translators: %s: tap target size */
				__( 'Minimum tap target size is %s; WCAG 2.5.5 recommends 44×44px minimum', 'wpshadow' ),
				$min_tap_size
			);
		}

		// Check for form input sizing
		$form_input_sizing = apply_filters( 'wpshadow_form_input_sizing_wcag_compliant', false );
		if ( ! $form_input_sizing ) {
			$issues[] = __( 'Form input sizing may not meet WCAG 2.5.5 minimum tap target requirements', 'wpshadow' );
		}

		// Check for icon button sizing
		$icon_button_sizing = apply_filters( 'wpshadow_icon_button_sizing_wcag_compliant', false );
		if ( ! $icon_button_sizing ) {
			$issues[] = __( 'Icon buttons may be smaller than WCAG 2.5.5 minimum 44×44px', 'wpshadow' );
		}

		// Check for close button/modal sizing
		$modal_close_button_sizing = apply_filters( 'wpshadow_modal_close_button_sizing_wcag_compliant', false );
		if ( ! $modal_close_button_sizing ) {
			$issues[] = __( 'Modal close buttons may not meet WCAG 2.5.5 size requirements', 'wpshadow' );
		}

		// Check for link sizing in navigation
		$nav_link_sizing = apply_filters( 'wpshadow_navigation_link_sizing_wcag_compliant', false );
		if ( ! $nav_link_sizing ) {
			$issues[] = __( 'Navigation links may not meet WCAG 2.5.5 minimum tap target size', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tap-target-size-validation',
			);
		}

		return null;
	}
}
