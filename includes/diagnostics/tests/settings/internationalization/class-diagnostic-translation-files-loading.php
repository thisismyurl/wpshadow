<?php
/**
 * Translation Files Loading Diagnostic
 *
 * Checks whether translation files are present and loaded for the active language.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Internationalization
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translation Files Loading Diagnostic Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Translation_Files_Loading extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'translation-files-loading';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Translation Files Not Loading or Incomplete';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether translation files load for the active language';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		$locale = $locale ? $locale : 'en_US';

		$site_language       = (string) get_option( 'WPLANG', '' );
		$available_languages = get_available_languages();

		$stats['locale']               = $locale;
		$stats['site_language']        = $site_language ? $site_language : 'not set';
		$stats['available_languages']  = $available_languages;
		$stats['language_count']       = count( $available_languages );

		$expects_translations = ( 'en_US' !== $locale ) || ! empty( $site_language ) || ! empty( $available_languages );
		if ( ! $expects_translations ) {
			return null;
		}

		$theme       = wp_get_theme();
		$text_domain = (string) $theme->get( 'TextDomain' );
		$domain_path = (string) $theme->get( 'DomainPath' );
		$domain_path = $domain_path ? $domain_path : '/languages';
		$domain_path = '/' . ltrim( $domain_path, '/' );

		$stats['theme_name']   = $theme->get( 'Name' );
		$stats['text_domain']  = $text_domain ? $text_domain : 'missing';
		$stats['domain_path']  = $domain_path;

		if ( empty( $text_domain ) ) {
			$issues[] = __( 'Theme text domain is missing, so translations cannot load', 'wpshadow' );
		} else {
			$expected_files = array();
			$theme_dirs     = array_unique( array( get_stylesheet_directory(), get_template_directory() ) );

			foreach ( $theme_dirs as $theme_dir ) {
				$expected_files[] = $theme_dir . $domain_path . '/' . $text_domain . '-' . $locale . '.mo';
			}

			$stats['expected_translation_files'] = $expected_files;
			$file_found = false;

			foreach ( $expected_files as $file_path ) {
				if ( file_exists( $file_path ) ) {
					$file_found = true;
					$stats['found_translation_file'] = $file_path;
					break;
				}
			}

			if ( ! $file_found ) {
				$issues[] = sprintf(
					/* translators: %s: locale code */
					__( 'No translation file found for %s in the active theme', 'wpshadow' ),
					$locale
				);
			}

			if ( ! is_textdomain_loaded( $text_domain ) ) {
				$issues[] = __( 'Theme translation files are not loaded for the active language', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site is set to a non-English language, but translation files are not fully loading. This can leave parts of the site in English and feels unfinished. Loading the correct translation files makes the experience consistent for visitors.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/translation-files-loading',
			'context'      => array(
				'stats'  => $stats,
				'issues' => $issues,
			),
		);
	}
}
