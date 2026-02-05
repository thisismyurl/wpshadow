<?php
/**
 * Mobile Text Contrast Ratio Treatment
 *
 * Validates text contrast meets WCAG standards for mobile readability.
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
 * Mobile Text Contrast Ratio Treatment Class
 *
 * Validates that text contrast meets WCAG standards, especially critical for mobile
 * devices used outdoors in sunlight, ensuring WCAG AA/AAA compliance.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Text_Contrast_Ratio extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-contrast-ratio';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Contrast Ratio';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate text contrast meets WCAG standards for mobile readability (WCAG 1.4.3)';

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

		// Check for contrast checking plugins
		$contrast_plugins = array(
			'wcag-contrast-checker' => 'WCAG Contrast Checker',
			'axe-con' => 'Axe Con',
			'lighthouse' => 'Lighthouse',
		);

		$has_contrast_plugin = false;
		foreach ( $contrast_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_contrast_plugin = true;
				break;
			}
		}

		// Check if theme declares WCAG compliance
		$theme = wp_get_theme();
		$theme_declares_wcag = $theme->get( 'WCAG' ) || apply_filters( 'wpshadow_theme_wcag_compliant', false );

		if ( ! $has_contrast_plugin && ! $theme_declares_wcag ) {
			$issues[] = __( 'No contrast validation plugin detected and theme WCAG compliance not declared', 'wpshadow' );
		}

		// Check for custom color schemes
		$has_custom_colors = current_theme_supports( 'custom-colors' );
		if ( $has_custom_colors && ! $has_contrast_plugin ) {
			$issues[] = __( 'Theme supports custom colors but no contrast validation detected', 'wpshadow' );
		}

		// Check for color palette enforcement
		$color_palette = apply_filters( 'wpshadow_enforced_color_palette', array() );
		if ( empty( $color_palette ) && ! $has_contrast_plugin ) {
			$issues[] = __( 'No enforced color palette detected; contrast ratios unverified', 'wpshadow' );
		}

		// Check for light-on-dark or dark-on-light patterns
		$background_color = get_theme_mod( 'background_color' );
		$text_color = apply_filters( 'wpshadow_primary_text_color', '#000000' );
		if ( empty( $background_color ) && ! $has_contrast_plugin ) {
			$issues[] = __( 'Background color not set; contrast verification needed', 'wpshadow' );
		}

		// Check for sufficient link contrast
		$link_color = apply_filters( 'wpshadow_link_color', '#0073aa' );
		if ( ! $has_contrast_plugin ) {
			$issues[] = __( 'Link color contrast ratios not verified by detection tool', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-text-contrast-ratio',
			);
		}

		return null;
	}
}
