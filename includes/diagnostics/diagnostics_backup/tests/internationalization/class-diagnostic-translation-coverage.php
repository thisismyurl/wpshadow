<?php
/**
 * Translation Coverage Diagnostic
 *
 * Measures percentage of interface strings translated. Incomplete translations
 * create unprofessional mixed-language experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1740
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Translation_Coverage Class
 *
 * Analyzes .po/.mo translation files to measure completion percentage
 * for active languages on the site.
 *
 * @since 1.6028.1740
 */
class Diagnostic_Translation_Coverage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'translation-coverage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Translation Coverage Below 80%';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures translation completeness for active languages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1740
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run if site has multiple languages or non-English locale.
		$site_locale = get_locale();
		if ( $site_locale === 'en_US' && ! self::has_multilingual_plugin() ) {
			return null; // English-only site, skip check.
		}

		$analysis = self::analyze_translation_coverage();

		if ( empty( $analysis['languages'] ) ) {
			return null; // No translations to analyze.
		}

		// Find lowest coverage language.
		$lowest_coverage = 100;
		foreach ( $analysis['languages'] as $lang ) {
			if ( $lang['coverage'] < $lowest_coverage ) {
				$lowest_coverage = $lang['coverage'];
			}
		}

		if ( $lowest_coverage >= 80 ) {
			return null; // All languages have good coverage.
		}

		// Determine severity based on coverage.
		if ( $lowest_coverage < 50 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} elseif ( $lowest_coverage < 80 ) {
			$severity     = 'low';
			$threat_level = 35;
		} else {
			$severity     = 'info';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: lowest coverage percentage, 2: language name */
				__( 'Lowest translation coverage: %1$s%% for %2$s', 'wpshadow' ),
				number_format( $lowest_coverage, 1 ),
				$analysis['lowest_language']['name']
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/translation-coverage',
			'family'      => self::$family,
			'meta'        => array(
				'lowest_coverage'   => round( $lowest_coverage, 1 ),
				'language_count'    => count( $analysis['languages'] ),
				'total_strings'     => $analysis['total_strings'],
				'recommended'       => __( '>80% translation coverage for all languages', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Complete .po file translations', 'wpshadow' ),
					__( 'Use Poedit or Loco Translate', 'wpshadow' ),
					__( 'Review untranslated strings', 'wpshadow' ),
					__( 'Regenerate .mo files', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Incomplete translations create unprofessional mixed-language experience. Users see some interface in their language, some in English. This breaks trust, confuses navigation, and reduces conversion. Professional sites should have >80% translation coverage before launching in a language.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Unprofessional Appearance: Mixed languages look unfinished', 'wpshadow' ),
					__( 'User Confusion: Interface inconsistency disrupts flow', 'wpshadow' ),
					__( 'Lost Trust: Users question site quality', 'wpshadow' ),
					__( 'Lower Conversion: Unclear CTAs and instructions', 'wpshadow' ),
				),
				'translation_analysis' => array(
					'languages'        => $analysis['languages'],
					'lowest_language'  => $analysis['lowest_language'],
					'total_strings'    => $analysis['total_strings'],
				),
				'coverage_scale' => array(
					'>95%'  => __( 'Excellent: Professional quality', 'wpshadow' ),
					'80-95%' => __( 'Good: Ready for market', 'wpshadow' ),
					'50-80%' => __( 'Warning: Incomplete experience', 'wpshadow' ),
					'<50%'  => __( 'Critical: Do not launch yet', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Poedit Editor', 'wpshadow' ),
						'description' => __( 'Complete translations with free Poedit software', 'wpshadow' ),
						'steps'       => array(
							__( 'Download Poedit (poedit.net) - free desktop app', 'wpshadow' ),
							__( 'Open .po file from wp-content/languages/', 'wpshadow' ),
							__( 'Translate missing strings (marked as fuzzy/untranslated)', 'wpshadow' ),
							__( 'Save file - Poedit auto-generates .mo', 'wpshadow' ),
							__( 'Upload updated .po/.mo files via FTP', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Loco Translate Plugin', 'wpshadow' ),
						'description' => __( 'Edit translations directly in WordPress admin', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Loco Translate plugin (free)', 'wpshadow' ),
							__( 'Go to Loco Translate → Themes/Plugins', 'wpshadow' ),
							__( 'Select language to edit', 'wpshadow' ),
							__( 'Translate missing strings in web interface', 'wpshadow' ),
							__( 'Click Sync to save changes', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Professional Translation Service', 'wpshadow' ),
						'description' => __( 'Hire native speakers for quality translations', 'wpshadow' ),
						'steps'       => array(
							__( 'Export .po file from site', 'wpshadow' ),
							__( 'Upload to translation service (Gengo, Weglot)', 'wpshadow' ),
							__( 'Professional translators complete strings', 'wpshadow' ),
							__( 'Download completed .po file', 'wpshadow' ),
							__( 'Upload to wp-content/languages/', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Complete 100% of core navigation strings first', 'wpshadow' ),
					__( 'Prioritize checkout and conversion flows', 'wpshadow' ),
					__( 'Use consistent terminology across translations', 'wpshadow' ),
					__( 'Have native speakers review translations', 'wpshadow' ),
					__( 'Don\'t launch language until >80% complete', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Switch site language in admin or WPML/Polylang', 'wpshadow' ),
						__( 'Navigate through site looking for English strings', 'wpshadow' ),
						__( 'Check cart, checkout, forms for completeness', 'wpshadow' ),
						__( 'Run this diagnostic to measure coverage', 'wpshadow' ),
					),
					'expected_result' => __( '>80% coverage for all active languages', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze translation coverage for active languages.
	 *
	 * @since  1.6028.1740
	 * @return array Analysis results with coverage by language.
	 */
	private static function analyze_translation_coverage() {
		$result = array(
			'languages'       => array(),
			'lowest_language' => array(
				'name'     => '',
				'coverage' => 100,
			),
			'total_strings'   => 0,
		);

		// Get available translations.
		$translations_dir = WP_LANG_DIR;
		
		if ( ! is_dir( $translations_dir ) ) {
			return $result;
		}

		// Check theme translations.
		$theme_textdomain = wp_get_theme()->get( 'TextDomain' );
		if ( $theme_textdomain ) {
			$theme_langs = self::analyze_textdomain_translations( $theme_textdomain, $translations_dir . '/themes/' );
			$result['languages'] = array_merge( $result['languages'], $theme_langs );
		}

		// Check plugin translations (limit to first 3 for performance).
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 3 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
			if ( ! empty( $plugin_data['TextDomain'] ) ) {
				$plugin_langs = self::analyze_textdomain_translations( 
					$plugin_data['TextDomain'], 
					$translations_dir . '/plugins/' 
				);
				$result['languages'] = array_merge( $result['languages'], $plugin_langs );
			}
		}

		// Find lowest coverage.
		foreach ( $result['languages'] as $lang ) {
			if ( $lang['coverage'] < $result['lowest_language']['coverage'] ) {
				$result['lowest_language'] = $lang;
			}
			if ( $lang['total'] > $result['total_strings'] ) {
				$result['total_strings'] = $lang['total'];
			}
		}

		return $result;
	}

	/**
	 * Analyze translations for a specific text domain.
	 *
	 * @since  1.6028.1740
	 * @param  string $textdomain Text domain to analyze.
	 * @param  string $directory  Directory containing .po files.
	 * @return array Language coverage data.
	 */
	private static function analyze_textdomain_translations( $textdomain, $directory ) {
		$languages = array();

		if ( ! is_dir( $directory ) ) {
			return $languages;
		}

		$po_files = glob( $directory . $textdomain . '-*.po' );

		foreach ( $po_files as $po_file ) {
			$coverage = self::calculate_po_coverage( $po_file );
			
			if ( $coverage !== null ) {
				// Extract locale from filename.
				preg_match( '/-([a-z]{2}_[A-Z]{2})\.po$/', $po_file, $matches );
				$locale = $matches[1] ?? 'unknown';

				$languages[] = array(
					'name'       => $locale,
					'textdomain' => $textdomain,
					'coverage'   => $coverage['percentage'],
					'total'      => $coverage['total'],
					'translated' => $coverage['translated'],
				);
			}
		}

		return $languages;
	}

	/**
	 * Calculate translation coverage for a .po file.
	 *
	 * @since  1.6028.1740
	 * @param  string $po_file Path to .po file.
	 * @return array|null Coverage statistics.
	 */
	private static function calculate_po_coverage( $po_file ) {
		$content = @file_get_contents( $po_file );
		if ( $content === false ) {
			return null;
		}

		// Count total msgid entries (original strings).
		preg_match_all( '/^msgid\s+"(.+)"$/m', $content, $msgids );
		$total = count( $msgids[0] );

		// Count translated msgstr entries (non-empty).
		preg_match_all( '/^msgstr\s+"(.+)"$/m', $content, $msgstrs );
		$translated = 0;
		foreach ( $msgstrs[1] as $msgstr ) {
			if ( ! empty( $msgstr ) ) {
				$translated++;
			}
		}

		$percentage = $total > 0 ? ( $translated / $total ) * 100 : 0;

		return array(
			'total'      => $total,
			'translated' => $translated,
			'percentage' => $percentage,
		);
	}

	/**
	 * Check if site has multilingual plugin active.
	 *
	 * @since  1.6028.1740
	 * @return bool True if multilingual plugin detected.
	 */
	private static function has_multilingual_plugin() {
		return defined( 'ICL_SITEPRESS_VERSION' ) || // WPML.
		       class_exists( 'Polylang' ) ||
		       function_exists( 'pll_languages_list' );
	}
}
