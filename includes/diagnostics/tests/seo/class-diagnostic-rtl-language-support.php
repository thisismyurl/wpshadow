<?php
/**
 * RTL Language Support Diagnostic
 *
 * Tests if site properly supports right-to-left languages like Arabic and Hebrew.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RTL Language Support Diagnostic Class
 *
 * Validates that the site properly handles right-to-left languages
 * including RTL stylesheets and directional adjustments.
 *
 * @since 1.6093.1200
 */
class Diagnostic_RTL_Language_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rtl-language-support';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'RTL Language Support';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site properly supports right-to-left languages like Arabic and Hebrew';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests RTL language support including rtl.css, direction-aware
	 * properties, and bidirectional text handling.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_dir = get_template_directory();

		// Check for rtl.css file.
		$has_rtl_css = file_exists( $theme_dir . '/rtl.css' );

		// Check if current locale is RTL.
		$current_locale = get_locale();
		$rtl_locales = array( 'ar', 'he_IL', 'fa_IR', 'ur', 'yi' );
		$is_rtl_locale = is_rtl();

		// Check main stylesheet for RTL-aware properties.
		$style_css = get_stylesheet_directory() . '/style.css';
		$uses_logical_properties = false;
		$uses_float_without_direction = false;

		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );

			// Check for logical properties (RTL-friendly).
			$logical_properties = array( 'margin-inline-start', 'margin-inline-end', 'padding-inline-start', 'inset-inline-start' );
			foreach ( $logical_properties as $property ) {
				if ( strpos( $style_content, $property ) !== false ) {
					$uses_logical_properties = true;
					break;
				}
			}

			// Check for problematic float usage.
			if ( strpos( $style_content, 'float: left' ) !== false || strpos( $style_content, 'float: right' ) !== false ) {
				$uses_float_without_direction = true;
			}
		}

		// Check RTL stylesheet content if exists.
		$rtl_rules_count = 0;
		if ( $has_rtl_css ) {
			$rtl_content = file_get_contents( $theme_dir . '/rtl.css' );
			preg_match_all( '/\{[^}]+\}/', $rtl_content, $matches );
			$rtl_rules_count = count( $matches[0] );
		}

		// Check functions.php for RTL editor style.
		$functions_file = $theme_dir . '/functions.php';
		$loads_rtl_editor_style = false;

		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			$loads_rtl_editor_style = ( strpos( $functions_content, 'add_editor_style' ) !== false ) &&
									( strpos( $functions_content, 'rtl' ) !== false );
		}

		// Check for dir attribute in HTML.
		$header_file = $theme_dir . '/header.php';
		$has_dir_attribute = false;

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$has_dir_attribute = ( strpos( $header_content, 'dir=' ) !== false ) ||
							   ( strpos( $header_content, 'language_attributes()' ) !== false );
		}

		// Check for text-align hardcoded to left/right.
		$hardcoded_text_align = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			if ( preg_match( '/text-align:\s*(left|right)[^-]/', $style_content ) ) {
				$hardcoded_text_align = true;
			}
		}

		// Check for absolute positioning issues.
		$absolute_positioning_issues = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			if ( preg_match( '/left:\s*\d+|right:\s*\d+/', $style_content ) ) {
				$absolute_positioning_issues = true;
			}
		}

		// Check for margin/padding direction-specific.
		$direction_specific_spacing = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			if ( preg_match( '/margin-(left|right):|padding-(left|right):/', $style_content ) ) {
				$direction_specific_spacing = true;
			}
		}

		// Check translation plugin RTL support.
		$translation_plugin_rtl = false;
		if ( is_plugin_active( 'polylang/polylang.php' ) || is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$translation_plugin_rtl = true;
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No rtl.css file.
		if ( ! $has_rtl_css ) {
			$issues[] = array(
				'type'        => 'no_rtl_css',
				'description' => __( 'No rtl.css file found; site layout will be broken for Arabic/Hebrew users', 'wpshadow' ),
			);
		}

		// Issue 2: rtl.css exists but has few rules.
		if ( $has_rtl_css && $rtl_rules_count < 10 ) {
			$issues[] = array(
				'type'        => 'insufficient_rtl_rules',
				'description' => sprintf(
					/* translators: %d: number of RTL CSS rules */
					__( 'rtl.css has only %d rules; likely insufficient for proper RTL support', 'wpshadow' ),
					$rtl_rules_count
				),
			);
		}

		// Issue 3: No dir attribute in HTML.
		if ( ! $has_dir_attribute ) {
			$issues[] = array(
				'type'        => 'no_dir_attribute',
				'description' => __( 'HTML lacks dir attribute or language_attributes(); RTL direction not set', 'wpshadow' ),
			);
		}

		// Issue 4: Hardcoded text-align.
		if ( $hardcoded_text_align && ! $has_rtl_css ) {
			$issues[] = array(
				'type'        => 'hardcoded_text_align',
				'description' => __( 'Text-align hardcoded to left/right without RTL overrides', 'wpshadow' ),
			);
		}

		// Issue 5: Direction-specific spacing without RTL overrides.
		if ( $direction_specific_spacing && ! $has_rtl_css ) {
			$issues[] = array(
				'type'        => 'direction_specific_spacing',
				'description' => __( 'Margin/padding uses left/right without RTL adjustments; spacing will be incorrect', 'wpshadow' ),
			);
		}

		// Issue 6: Float usage without RTL consideration.
		if ( $uses_float_without_direction && ! $has_rtl_css ) {
			$issues[] = array(
				'type'        => 'float_without_rtl',
				'description' => __( 'CSS uses float:left/right without RTL overrides; layout will mirror incorrectly', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site does not properly support RTL languages, making it unusable for 500+ million Arabic and Hebrew speakers', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rtl-language-support',
				'details'      => array(
					'has_rtl_css'             => $has_rtl_css,
					'rtl_rules_count'         => $rtl_rules_count,
					'is_rtl_locale'           => $is_rtl_locale,
					'current_locale'          => $current_locale,
					'has_dir_attribute'       => $has_dir_attribute,
					'loads_rtl_editor_style'  => $loads_rtl_editor_style,
					'uses_logical_properties' => $uses_logical_properties,
					'hardcoded_text_align'    => $hardcoded_text_align,
					'absolute_positioning_issues' => $absolute_positioning_issues,
					'direction_specific_spacing' => $direction_specific_spacing,
					'uses_float_without_direction' => $uses_float_without_direction,
					'translation_plugin_rtl'  => $translation_plugin_rtl,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Create rtl.css, use logical CSS properties, add dir attribute, test with RTL locale', 'wpshadow' ),
					'rtl_css_example'         => array(
						'body'      => 'direction: rtl; text-align: right;',
						'.alignleft' => 'float: right; margin: 0 0 1em 1em;',
						'.alignright' => 'float: left; margin: 0 1em 1em 0;',
					),
					'logical_properties'      => array(
						'margin-left'   => 'Use margin-inline-start',
						'margin-right'  => 'Use margin-inline-end',
						'padding-left'  => 'Use padding-inline-start',
						'padding-right' => 'Use padding-inline-end',
						'left'          => 'Use inset-inline-start',
						'right'         => 'Use inset-inline-end',
					),
					'dir_attribute_code'      => '<html <?php language_attributes(); ?>>',
					'rtl_languages'           => array(
						'Arabic (ar)'    => '422 million speakers',
						'Hebrew (he_IL)' => '9 million speakers',
						'Persian (fa_IR)' => '110 million speakers',
						'Urdu (ur)'      => '230 million speakers',
					),
					'rtl_testing_tools'       => array(
						'Chrome DevTools' => 'Settings > Elements > Force RTL',
						'Firefox'         => 'about:config > intl.uidirection > 1',
						'Browser plugins' => 'Switch Direction',
						'WordPress'       => 'Change locale to he_IL or ar',
					),
				),
			);
		}

		return null;
	}
}
