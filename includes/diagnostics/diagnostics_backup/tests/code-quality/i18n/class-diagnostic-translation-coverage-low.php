<?php
/**
 * Translation Coverage Below 80% Diagnostic
 *
 * Measures percentage of strings translated in active language files.
 * Incomplete translations create unprofessional mixed-language experiences.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\I18n
 * @since      1.6028.2149
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Translation_Coverage_Low Class
 *
 * Analyzes .po files to determine translation completeness percentage.
 * Low coverage indicates poor localization quality.
 *
 * @since 1.6028.2149
 */
class Diagnostic_Translation_Coverage_Low extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'translation-coverage-low';

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
	protected static $family = 'i18n';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2149
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_translation_coverage' );
		if ( false !== $cached ) {
			return $cached;
		}

		$locale = get_locale();

		// Skip check for English sites.
		if ( 'en_US' === $locale || 'en' === $locale ) {
			set_transient( 'wpshadow_diagnostic_translation_coverage', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$coverage_data = self::analyze_translation_coverage( $locale );

		if ( ! $coverage_data || $coverage_data['coverage_percent'] >= 80 ) {
			set_transient( 'wpshadow_diagnostic_translation_coverage', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$coverage_percent = $coverage_data['coverage_percent'];
		$severity         = $coverage_percent < 50 ? 'high' : 'medium';
		$threat_level     = 80 - $coverage_percent;

		$finding = array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: coverage percent, 2: locale */
				__( 'Translation coverage is only %1$s%% for locale %2$s, creating mixed-language experience', 'wpshadow' ),
				number_format( $coverage_percent, 1 ),
				$locale
			),
			'severity'       => $severity,
			'threat_level'   => $threat_level,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/translation-coverage',
			'meta'           => array(
				'locale'            => $locale,
				'coverage_percent'  => round( $coverage_percent, 2 ),
				'total_strings'     => $coverage_data['total_strings'],
				'translated_strings' => $coverage_data['translated_strings'],
				'untranslated_strings' => $coverage_data['untranslated_strings'],
			),
			'details'        => array(
				sprintf(
					/* translators: %s: coverage percent */
					__( 'Translation coverage: %s%%', 'wpshadow' ),
					number_format( $coverage_percent, 1 )
				),
				sprintf(
					/* translators: %d: number of strings */
					__( 'Untranslated strings: %d', 'wpshadow' ),
					$coverage_data['untranslated_strings']
				),
				__( 'Mixed-language content appears unprofessional to users', 'wpshadow' ),
			),
			'recommendations' => array(
				__( 'Complete theme and plugin translations using Poedit or Loco Translate', 'wpshadow' ),
				__( 'Review .po files in wp-content/languages/', 'wpshadow' ),
				__( 'Consider using automatic translation services for quick completion', 'wpshadow' ),
				__( 'Test site in target language to identify untranslated strings', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_translation_coverage', $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Analyze translation coverage for locale.
	 *
	 * Scans .po files to count translated vs total strings.
	 *
	 * @since  1.6028.2149
	 * @param  string $locale Locale code.
	 * @return array|false Coverage data or false.
	 */
	private static function analyze_translation_coverage( $locale ) {
		$lang_dir = WP_LANG_DIR;
		$po_files = glob( $lang_dir . '/*-' . $locale . '.po' );

		if ( empty( $po_files ) ) {
			return false;
		}

		$total_strings       = 0;
		$translated_strings  = 0;

		foreach ( $po_files as $po_file ) {
			if ( ! file_exists( $po_file ) || ! is_readable( $po_file ) ) {
				continue;
			}

			$content = file_get_contents( $po_file );
			if ( false === $content ) {
				continue;
			}

			// Count msgid entries (total strings).
			preg_match_all( '/^msgid\s+"(.+)"/m', $content, $msgid_matches );
			$file_total = count( $msgid_matches[0] );

			// Count msgstr entries with translations (not empty).
			preg_match_all( '/^msgstr\s+"(.+)"/m', $content, $msgstr_matches );
			$file_translated = 0;
			foreach ( $msgstr_matches[1] as $msgstr ) {
				if ( ! empty( $msgstr ) ) {
					$file_translated++;
				}
			}

			$total_strings      += $file_total;
			$translated_strings += $file_translated;
		}

		if ( 0 === $total_strings ) {
			return false;
		}

		$coverage_percent = ( $translated_strings / $total_strings ) * 100;

		return array(
			'total_strings'        => $total_strings,
			'translated_strings'   => $translated_strings,
			'untranslated_strings' => $total_strings - $translated_strings,
			'coverage_percent'     => $coverage_percent,
		);
	}
}
