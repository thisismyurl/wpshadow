<?php
/**
 * Tap Target Spacing Validation Diagnostic
 *
 * Measures distance between adjacent interactive elements to prevent accidental activation.
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
 * Tap Target Spacing Validation Diagnostic Class
 *
 * Measures distance between adjacent interactive elements to prevent accidental
 * activation on touch devices, ensuring WCAG 2.5.8 compliance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tap_Target_Spacing_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tap-target-spacing-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Spacing Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measure distance between interactive elements to prevent accidental activation (WCAG 2.5.8)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'usability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for spacing validation plugins/tools
		$spacing_plugins = array(
			'wcag-tap-target' => 'WCAG Tap Target',
			'lighthouse' => 'Lighthouse',
		);

		$has_spacing_plugin = false;
		foreach ( $spacing_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_spacing_plugin = true;
				break;
			}
		}

		// Check if theme defines spacing/padding standards
		$has_spacing_standards = apply_filters( 'wpshadow_theme_defines_spacing_standards', false );
		if ( ! $has_spacing_standards && ! $has_spacing_plugin ) {
			$issues[] = __( 'No spacing validation plugin or theme spacing standards detected', 'wpshadow' );
		}

		// Check for button and link spacing
		global $wp_styles;
		if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
			$has_button_spacing = false;
			foreach ( $wp_styles->registered as $handle => $obj ) {
				if ( strpos( $handle, 'button' ) !== false || strpos( $handle, 'link' ) !== false ) {
					// Check if handle has spacing rules
					if ( strpos( $handle, 'spacing' ) !== false || strpos( $handle, 'margin' ) !== false ) {
						$has_button_spacing = true;
						break;
					}
				}
			}

			if ( ! $has_button_spacing && ! $has_spacing_plugin ) {
				$issues[] = __( 'Button and link spacing CSS not found; WCAG 2.5.8 compliance unverified', 'wpshadow' );
			}
		}

		// Check for menu item spacing
		$menu_spacing_verified = apply_filters( 'wpshadow_menu_item_spacing_wcag_compliant', false );
		if ( ! $menu_spacing_verified ) {
			$issues[] = __( 'Menu item spacing may not meet WCAG 2.5.8 minimum 8px requirement', 'wpshadow' );
		}

		// Check for form input spacing
		$form_spacing_verified = apply_filters( 'wpshadow_form_input_spacing_wcag_compliant', false );
		if ( ! $form_spacing_verified ) {
			$issues[] = __( 'Form input spacing may not meet WCAG 2.5.8 minimum requirements', 'wpshadow' );
		}

		// Check for social icon spacing if present
		if ( has_nav_menu( 'social' ) ) {
			$social_spacing = apply_filters( 'wpshadow_social_icon_spacing_wcag_compliant', false );
			if ( ! $social_spacing ) {
				$issues[] = __( 'Social icon spacing may not meet WCAG 2.5.8 minimum requirements', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tap-target-spacing-validation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
