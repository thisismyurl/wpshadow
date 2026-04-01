<?php
/**
 * Missing Customizer Settings in Export Diagnostic
 *
 * Detects when theme customizer settings (colors, fonts, layouts)
 * are excluded from exports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Customizer Settings in Export Diagnostic Class
 *
 * Detects when theme customizer settings are excluded from exports.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Customizer_Settings_In_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-customizer-settings-in-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Customizer Settings in Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects theme customizer settings excluded from exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that theme customizer settings are properly
	 * captured in export files.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get current theme.
		$current_theme = get_template();
		$current_stylesheet = get_stylesheet();

		// Check for theme mod customizations.
		$theme_mods = get_option( 'theme_mods_' . $current_stylesheet, array() );

		if ( empty( $theme_mods ) || ! is_array( $theme_mods ) ) {
			return null;
		}

		$theme_mod_count = count( $theme_mods );
		$logo_customized = false;
		$colors_customized = false;
		$fonts_customized = false;
		$header_customized = false;
		$footer_customized = false;

		// Check for specific customizations.
		foreach ( $theme_mods as $key => $value ) {
			if ( strpos( $key, 'logo' ) !== false || strpos( $key, 'custom_logo' ) !== false ) {
				$logo_customized = true;
			}
			if ( strpos( $key, 'color' ) !== false ) {
				$colors_customized = true;
			}
			if ( strpos( $key, 'font' ) !== false || strpos( $key, 'typography' ) !== false ) {
				$fonts_customized = true;
			}
			if ( strpos( $key, 'header' ) !== false ) {
				$header_customized = true;
			}
			if ( strpos( $key, 'footer' ) !== false ) {
				$footer_customized = true;
			}
		}

		// Check for custom CSS.
		$custom_css = wp_get_custom_css();
		$custom_css_length = strlen( $custom_css );

		// Check for custom CSS post.
		$custom_css_post = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_type = %s
				LIMIT 1",
				'custom_css'
			)
		);

		// Check for customizer option changes.
		$customizer_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE %s",
				'theme_mods_%'
			)
		);

		$customizer_option_count = count( $customizer_options );

		// Check for theme customizer publishes.
		$customizer_publishes = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				WHERE meta_key = %s",
				'_customize_changeset_uuid'
			)
		);

		// Check for background image customizations.
		$background_image = get_theme_mod( 'background_image' );
		$background_color = get_theme_mod( 'background_color' );

		// Check for header image customizations.
		$header_image = get_theme_mod( 'header_image' );

		// Check WXR customizer export support.
		$wxr_theme_mods_included = apply_filters( 'wxr_export_theme_mods', false );

		// Check for theme options page (some themes have separate option pages).
		$theme_option_keys = array(
			'theme_options',
			get_template() . '_options',
			get_stylesheet() . '_options',
		);

		$theme_options_count = 0;
		foreach ( $theme_option_keys as $key ) {
			$option = get_option( $key );
			if ( ! empty( $option ) ) {
				$theme_options_count++;
			}
		}

		if ( $theme_mod_count > 0 || $custom_css_length > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of customizer settings */
					__( '%d customizer settings and design changes are not included in exports', 'wpshadow' ),
					$theme_mod_count
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing-customizer-settings-in-export?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'total_theme_mods'               => $theme_mod_count,
					'customizer_options_stored'      => $customizer_option_count,
					'custom_css_length'              => $custom_css_length,
					'custom_css_post_found'          => (bool) $custom_css_post,
					'theme_option_pages_found'       => $theme_options_count,
					'customizer_publishes'           => $customizer_publishes,
					'logo_customized'                => $logo_customized,
					'colors_customized'              => $colors_customized,
					'fonts_customized'               => $fonts_customized,
					'header_customized'              => $header_customized,
					'footer_customized'              => $footer_customized,
					'background_image_set'           => (bool) $background_image,
					'background_color_set'           => (bool) $background_color,
					'header_image_set'               => (bool) $header_image,
					'wxr_theme_mods_export_enabled'  => $wxr_theme_mods_included,
					'brand_impact'                   => __( 'Site branding (colors, fonts, logo) will revert to theme defaults', 'wpshadow' ),
					'design_loss'                    => sprintf(
						/* translators: %d: number of customizations */
						__( '%d custom design settings will be lost', 'wpshadow' ),
						$theme_mod_count
					),
					'css_loss'                       => $custom_css_length > 0 ? sprintf(
						/* translators: %s: CSS size */
						__( '%s of custom CSS will be lost', 'wpshadow' ),
						size_format( $custom_css_length )
					) : '',
					'reconfiguration_effort'         => __( 'Hours of manual design reconfiguration required', 'wpshadow' ),
					'business_impact'                => __( 'Brand consistency and professional appearance significantly degraded', 'wpshadow' ),
					'fix_methods'                    => array(
						__( 'Use Customizer backup/export if theme provides it', 'wpshadow' ),
						__( 'Export custom CSS separately before backup', 'wpshadow' ),
						__( 'Document all customizer settings with screenshots', 'wpshadow' ),
						__( 'Use theme-specific export if available', 'wpshadow' ),
						__( 'Use database backup which includes theme_mods', 'wpshadow' ),
					),
					'verification'                   => array(
						__( 'Check WXR export for theme_mods entries', 'wpshadow' ),
						__( 'Verify custom CSS is included in export', 'wpshadow' ),
						__( 'Document current customizer settings', 'wpshadow' ),
						__( 'Screenshot current site design before export', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Compare design post-import vs pre-export', 'wpshadow' ),
					),
					'critical_note'                  => __( 'Customizer settings are not included in standard WordPress export - design will be lost unless explicitly backed up', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
