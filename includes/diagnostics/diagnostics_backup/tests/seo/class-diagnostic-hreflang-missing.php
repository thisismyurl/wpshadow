<?php
/**
 * Hreflang Tags Missing Diagnostic
 *
 * Detects missing hreflang tags for multi-language sites.
 * Hreflang prevents wrong language in search results.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1840
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Hreflang_Missing Class
 *
 * Checks for hreflang implementation on multilingual sites.
 *
 * @since 1.6028.1840
 */
class Diagnostic_Hreflang_Missing extends Diagnostic_Base {

	protected static $slug = 'hreflang-missing';
	protected static $title = 'Hreflang Tags Missing for Multi-language Content';
	protected static $description = 'Checks for hreflang tags on multilingual sites';
	protected static $family = 'seo';

	public static function check() {
		// Check if multilingual.
		$is_multilingual = defined( 'ICL_SITEPRESS_VERSION' ) || function_exists( 'pll_languages_list' ) || class_exists( 'TRP_Translate_Press' );

		if ( ! $is_multilingual ) {
			return null; // Not multilingual.
		}

		$hreflang_status = self::check_hreflang_implementation();

		if ( $hreflang_status['has_hreflang'] ) {
			return null; // Hreflang implemented.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Multilingual site missing hreflang tags for international SEO', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/hreflang',
			'family'      => self::$family,
			'meta'        => array(
				'plugin'            => $hreflang_status['plugin'],
				'languages'         => $hreflang_status['languages'],
				'recommended'       => __( 'Add hreflang tags to all language versions', 'wpshadow' ),
				'impact_level'      => 'high',
				'immediate_actions' => array(
					__( 'Enable hreflang in WPML/Polylang', 'wpshadow' ),
					__( 'Add self-referential hreflang', 'wpshadow' ),
					__( 'Verify with Google Search Console', 'wpshadow' ),
					__( 'Test with hreflang validator', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Hreflang tags tell Google which language version to show users. Without them, English speakers may see Spanish pages, German users get French content. This destroys UX and SEO. Hreflang improves international rankings 30-40%, reduces bounce rate, and ensures correct content reaches correct audience. Required for multilingual SEO.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Wrong Language in SERPs: Users see irrelevant results', 'wpshadow' ),
					__( 'International SEO Issues: Languages compete with each other', 'wpshadow' ),
					__( 'Higher Bounce Rate: Users leave when language is wrong', 'wpshadow' ),
					__( 'Lost Traffic: Translations not indexed properly', 'wpshadow' ),
				),
				'hreflang_status' => $hreflang_status,
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Enable in WPML/Polylang', 'wpshadow' ),
						'description' => __( 'Multilingual plugins include hreflang', 'wpshadow' ),
						'steps'       => array(
							__( 'WPML: Settings → SEO → Enable hreflang', 'wpshadow' ),
							__( 'Polylang: Settings → URL modifications → Enable', 'wpshadow' ),
							__( 'View page source to verify <link rel="alternate" hreflang=...', 'wpshadow' ),
							__( 'Test with hreflang validator', 'wpshadow' ),
							__( 'Submit to Google Search Console', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Yoast SEO + WPML', 'wpshadow' ),
						'description' => __( 'Combined solution for optimal implementation', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO Premium', 'wpshadow' ),
							__( 'Install WPML (already active)', 'wpshadow' ),
							__( 'Yoast auto-detects WPML languages', 'wpshadow' ),
							__( 'Adds proper hreflang with x-default', 'wpshadow' ),
							__( 'Includes self-referencing tags', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Custom Hreflang Implementation', 'wpshadow' ),
						'description' => __( 'Manual hreflang in theme', 'wpshadow' ),
						'steps'       => array(
							__( 'Add to wp_head hook', 'wpshadow' ),
							__( 'Get all language versions of current page', 'wpshadow' ),
							__( 'Output: <link rel="alternate" hreflang="en" href="URL">', 'wpshadow' ),
							__( 'Include x-default for default language', 'wpshadow' ),
							__( 'Add self-referencing hreflang', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Include self-referencing hreflang', 'wpshadow' ),
					__( 'Add x-default for default/fallback language', 'wpshadow' ),
					__( 'Use language-region codes (en-US, en-GB)', 'wpshadow' ),
					__( 'Verify bidirectional links (EN→FR and FR→EN)', 'wpshadow' ),
					__( 'Monitor Google Search Console for errors', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'View page source on multilingual page', 'wpshadow' ),
						__( 'Search for <link rel="alternate" hreflang', 'wpshadow' ),
						__( 'Verify all languages listed', 'wpshadow' ),
						__( 'Use: https://validator.schema.org/', 'wpshadow' ),
						__( 'Check Google Search Console → International Targeting', 'wpshadow' ),
					),
					'expected_result' => __( 'Hreflang tags present for all language versions', 'wpshadow' ),
				),
			),
		);
	}

	private static function check_hreflang_implementation() {
		$result = array(
			'has_hreflang' => false,
			'plugin'       => '',
			'languages'    => array(),
		);

		// Check WPML.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$result['plugin'] = 'WPML';
			// WPML typically auto-adds hreflang if enabled in settings.
			// Check if WPML SEO option is on.
			$wpml_settings = get_option( 'icl_sitepress_settings', array() );
			if ( ! empty( $wpml_settings['auto_adjust_ids'] ) ) {
				$result['has_hreflang'] = true;
			}
		}

		// Check Polylang.
		if ( function_exists( 'pll_languages_list' ) ) {
			$result['plugin'] = 'Polylang';
			$result['languages'] = pll_languages_list( array( 'fields' => 'name' ) );
			// Polylang Pro adds hreflang automatically.
			if ( function_exists( 'PLL' ) && PLL()->model->get_languages_list() ) {
				$result['has_hreflang'] = true;
			}
		}

		// Check TranslatePress.
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			$result['plugin'] = 'TranslatePress';
			// TranslatePress adds hreflang by default.
			$result['has_hreflang'] = true;
		}

		return $result;
	}
}
