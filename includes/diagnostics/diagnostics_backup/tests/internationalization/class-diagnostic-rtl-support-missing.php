<?php
/**
 * RTL Language Support Diagnostic
 *
 * Detects missing right-to-left (RTL) CSS support for Arabic, Hebrew,
 * and other RTL languages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1820
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_RTL_Support_Missing Class
 *
 * Checks if theme has RTL language support.
 *
 * @since 1.6028.1820
 */
class Diagnostic_RTL_Support_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rtl-support-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'RTL Language Support Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for right-to-left language CSS support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1820
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site uses RTL language or has multilingual setup.
		$needs_rtl = self::site_needs_rtl();

		if ( ! $needs_rtl['needed'] ) {
			return null; // RTL not needed for current setup.
		}

		$rtl_support = self::check_rtl_support();

		if ( $rtl_support['has_support'] ) {
			return null; // RTL support is present.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: language name */
				__( 'Site uses %s but lacks RTL stylesheet', 'wpshadow' ),
				$needs_rtl['reason']
			),
			'severity'    => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/rtl-support',
			'family'      => self::$family,
			'meta'        => array(
				'site_locale'       => $needs_rtl['locale'],
				'rtl_languages'     => $needs_rtl['rtl_languages'],
				'recommended'       => __( 'Add rtl.css stylesheet for RTL language support', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Create style-rtl.css in theme', 'wpshadow' ),
					__( 'Or use CSS logical properties', 'wpshadow' ),
					__( 'Test site in RTL language', 'wpshadow' ),
					__( 'Fix navigation and form alignment', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'RTL languages (Arabic, Hebrew, Persian, Urdu) require mirrored layouts. Without RTL CSS, navigation menus appear backwards, forms are misaligned, text flows wrong direction, and the site becomes unusable. This blocks entire markets: Middle East (400M+ people), Israel (9M+). Professional multilingual sites must support RTL. WordPress has built-in RTL detection.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Unusable Interface: Navigation backwards, forms broken', 'wpshadow' ),
					__( 'Market Exclusion: Entire regions can\'t use site', 'wpshadow' ),
					__( 'Lost Revenue: Middle East e-commerce inaccessible', 'wpshadow' ),
					__( 'Poor Experience: Text direction feels wrong', 'wpshadow' ),
				),
				'rtl_analysis'  => $rtl_support,
				'affected_languages' => array(
					'ar' => __( 'Arabic (400M+ speakers)', 'wpshadow' ),
					'he' => __( 'Hebrew (9M+ speakers)', 'wpshadow' ),
					'fa' => __( 'Persian/Farsi (110M+ speakers)', 'wpshadow' ),
					'ur' => __( 'Urdu (230M+ speakers)', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Create style-rtl.css File', 'wpshadow' ),
						'description' => __( 'WordPress auto-loads rtl.css for RTL languages', 'wpshadow' ),
						'steps'       => array(
							__( 'Copy style.css to style-rtl.css in theme directory', 'wpshadow' ),
							__( 'Reverse float: left → right, right → left', 'wpshadow' ),
							__( 'Reverse text-align: left → right, right → left', 'wpshadow' ),
							__( 'Flip margin/padding: margin-left → margin-right', 'wpshadow' ),
							__( 'Test with RTL language: define(\'WP_LANG\', \'ar\')', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Use CSS Logical Properties', 'wpshadow' ),
						'description' => __( 'Modern CSS handles RTL automatically', 'wpshadow' ),
						'steps'       => array(
							__( 'Replace margin-left with margin-inline-start', 'wpshadow' ),
							__( 'Replace margin-right with margin-inline-end', 'wpshadow' ),
							__( 'Replace padding-left with padding-inline-start', 'wpshadow' ),
							__( 'Use text-align: start instead of left', 'wpshadow' ),
							__( 'No separate RTL file needed', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'RTLCSS Automatic Conversion', 'wpshadow' ),
						'description' => __( 'Build tool generates RTL CSS automatically', 'wpshadow' ),
						'steps'       => array(
							__( 'Install rtlcss: npm install rtlcss', 'wpshadow' ),
							__( 'Add build script: "rtl": "rtlcss style.css style-rtl.css"', 'wpshadow' ),
							__( 'Run npm run rtl during build', 'wpshadow' ),
							__( 'Auto-generates mirrored CSS', 'wpshadow' ),
							__( 'Commit both files to repo', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Use CSS logical properties (margin-inline-start) for new code', 'wpshadow' ),
					__( 'Test with is_rtl() WordPress function', 'wpshadow' ),
					__( 'Don\'t mirror icons/logos (use /*rtl:ignore*/ comment)', 'wpshadow' ),
					__( 'Check form alignment in RTL mode', 'wpshadow' ),
					__( 'Verify navigation menu direction', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Add define(\'WPLANG\', \'ar\'); to wp-config.php', 'wpshadow' ),
						__( 'Or switch language in Settings → General', 'wpshadow' ),
						__( 'Download Arabic language files', 'wpshadow' ),
						__( 'Visit site frontend', 'wpshadow' ),
						__( 'Verify layout mirrors correctly', 'wpshadow' ),
					),
					'expected_result' => __( 'Site layout mirrors properly for RTL languages', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if site needs RTL support.
	 *
	 * @since  1.6028.1820
	 * @return array RTL requirement analysis.
	 */
	private static function site_needs_rtl() {
		$result = array(
			'needed'        => false,
			'reason'        => '',
			'locale'        => get_locale(),
			'rtl_languages' => array(),
		);

		// RTL language locales.
		$rtl_locales = array(
			'ar'    => 'Arabic',
			'ar_AE' => 'Arabic (UAE)',
			'ar_SA' => 'Arabic (Saudi Arabia)',
			'he_IL' => 'Hebrew',
			'fa_IR' => 'Persian',
			'ur'    => 'Urdu',
		);

		// Check current site locale.
		$current_locale = $result['locale'];
		if ( isset( $rtl_locales[ $current_locale ] ) ) {
			$result['needed'] = true;
			$result['reason'] = $rtl_locales[ $current_locale ];
			$result['rtl_languages'][] = $rtl_locales[ $current_locale ];
			return $result;
		}

		// Check for multilingual plugins with RTL languages.
		if ( function_exists( 'icl_get_languages' ) ) {
			// WPML.
			$languages = icl_get_languages( 'skip_missing=0' );
			foreach ( $languages as $lang ) {
				if ( isset( $rtl_locales[ $lang['default_locale'] ] ) ) {
					$result['needed'] = true;
					$result['reason'] = 'WPML with ' . $rtl_locales[ $lang['default_locale'] ];
					$result['rtl_languages'][] = $rtl_locales[ $lang['default_locale'] ];
				}
			}
		}

		if ( function_exists( 'pll_languages_list' ) ) {
			// Polylang.
			$languages = pll_languages_list( array( 'fields' => 'locale' ) );
			foreach ( $languages as $locale ) {
				if ( isset( $rtl_locales[ $locale ] ) ) {
					$result['needed'] = true;
					$result['reason'] = 'Polylang with ' . $rtl_locales[ $locale ];
					$result['rtl_languages'][] = $rtl_locales[ $locale ];
				}
			}
		}

		return $result;
	}

	/**
	 * Check if theme has RTL support.
	 *
	 * @since  1.6028.1820
	 * @return array RTL support analysis.
	 */
	private static function check_rtl_support() {
		$result = array(
			'has_support'     => false,
			'method'          => '',
			'rtl_file_exists' => false,
			'uses_logical'    => false,
		);

		$theme_dir = get_template_directory();

		// Check for style-rtl.css (WordPress standard).
		$rtl_file_paths = array(
			$theme_dir . '/style-rtl.css',
			$theme_dir . '/css/style-rtl.css',
			$theme_dir . '/assets/css/style-rtl.css',
			$theme_dir . '/rtl.css',
		);

		foreach ( $rtl_file_paths as $path ) {
			if ( file_exists( $path ) ) {
				$result['has_support'] = true;
				$result['method'] = 'RTL Stylesheet';
				$result['rtl_file_exists'] = true;
				return $result;
			}
		}

		// Check if main stylesheet uses CSS logical properties.
		$main_css = $theme_dir . '/style.css';
		if ( file_exists( $main_css ) ) {
			$css_content = @file_get_contents( $main_css );
			if ( $css_content ) {
				// Check for logical properties.
				$logical_props = array(
					'margin-inline-start',
					'margin-inline-end',
					'padding-inline-start',
					'padding-inline-end',
					'inset-inline-start',
					'inset-inline-end',
				);

				foreach ( $logical_props as $prop ) {
					if ( strpos( $css_content, $prop ) !== false ) {
						$result['has_support'] = true;
						$result['method'] = 'CSS Logical Properties';
						$result['uses_logical'] = true;
						return $result;
					}
				}
			}
		}

		return $result;
	}
}
