<?php
/**
 * Translation Readiness Diagnostic
 *
 * Tests if site is properly prepared for translation and localization.
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
 * Translation Readiness Diagnostic Class
 *
 * Validates that the site is properly prepared for translation including
 * text domain usage, translation functions, and language files.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Translation_Readiness extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'translation-readiness';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Translation Readiness';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is properly prepared for translation and localization';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests translation readiness including text domain, translation
	 * functions, .pot files, and language settings.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );
		$theme_dir = get_template_directory();
		$text_domain = $theme->get( 'TextDomain' );

		// Check for language files.
		$languages_dir = $theme_dir . '/languages';
		$has_languages_dir = is_dir( $languages_dir );

		$pot_files = array();
		$po_files = array();
		$mo_files = array();

		if ( $has_languages_dir ) {
			$files = scandir( $languages_dir );
			foreach ( $files as $file ) {
				if ( substr( $file, -4 ) === '.pot' ) {
					$pot_files[] = $file;
				} elseif ( substr( $file, -3 ) === '.po' ) {
					$po_files[] = $file;
				} elseif ( substr( $file, -3 ) === '.mo' ) {
					$mo_files[] = $file;
				}
			}
		}

		// Check theme functions.php for load_theme_textdomain.
		$functions_file = $theme_dir . '/functions.php';
		$loads_textdomain = false;

		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			$loads_textdomain = ( strpos( $functions_content, 'load_theme_textdomain' ) !== false ) ||
							  ( strpos( $functions_content, 'load_plugin_textdomain' ) !== false );
		}

		// Check for hardcoded strings in theme files.
		$hardcoded_strings = 0;
		$theme_files = array( 'header.php', 'footer.php', 'sidebar.php', 'index.php' );

		foreach ( $theme_files as $file ) {
			$filepath = $theme_dir . '/' . $file;
			if ( file_exists( $filepath ) ) {
				$content = file_get_contents( $filepath );
				// Look for echo statements with hardcoded strings (simplified detection).
				preg_match_all( '/echo\s+["\']([^"\']+)["\']/', $content, $matches );
				// Filter out common non-translatable strings.
				foreach ( $matches[1] as $string ) {
					$string_trimmed = trim( $string );
					if ( strlen( $string_trimmed ) > 3 && ! is_numeric( $string_trimmed ) ) {
						// Check if string contains words (not just HTML).
						if ( preg_match( '/[a-zA-Z]{3,}/', $string_trimmed ) ) {
							$hardcoded_strings++;
						}
					}
				}
			}
		}

		// Check WordPress language setting.
		$site_language = get_locale();
		$is_english_only = ( $site_language === 'en_US' );

		// Check for translation plugins.
		$translation_plugins = array(
			'polylang/polylang.php'       => 'Polylang',
			'sitepress-multilingual-cms/sitepress.php' => 'WPML',
			'translatepress-multilingual/index.php' => 'TranslatePress',
			'weglot/weglot.php'           => 'Weglot',
		);

		$active_translation_plugins = array();
		foreach ( $translation_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_translation_plugins[] = $name;
			}
		}

		// Check for translation functions usage.
		$uses_translation_functions = false;
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			$uses_translation_functions = ( strpos( $functions_content, '__(' ) !== false ) ||
										( strpos( $functions_content, '_e(' ) !== false ) ||
										( strpos( $functions_content, 'esc_html__(' ) !== false ) ||
										( strpos( $functions_content, 'esc_html_e(' ) !== false );
		}

		// Check GlotPress integration.
		$has_glotpress = is_plugin_active( 'glotpress/glotpress.php' );

		// Check for RTL stylesheet.
		$has_rtl_stylesheet = file_exists( $theme_dir . '/rtl.css' );

		// Check for language switcher.
		$has_language_switcher = ! empty( $active_translation_plugins ) || has_nav_menu( 'language-switcher' );

		// Check for issues.
		$issues = array();

		// Issue 1: No languages directory.
		if ( ! $has_languages_dir ) {
			$issues[] = array(
				'type'        => 'no_languages_dir',
				'description' => __( 'Theme has no /languages directory; translation files cannot be stored', 'wpshadow' ),
			);
		}

		// Issue 2: No .pot file.
		if ( empty( $pot_files ) ) {
			$issues[] = array(
				'type'        => 'no_pot_file',
				'description' => __( 'No .pot template file found; translators cannot create translations', 'wpshadow' ),
			);
		}

		// Issue 3: Text domain not loaded.
		if ( ! $loads_textdomain ) {
			$issues[] = array(
				'type'        => 'textdomain_not_loaded',
				'description' => __( 'Theme does not call load_theme_textdomain(); translations will not work', 'wpshadow' ),
			);
		}

		// Issue 4: Hardcoded strings detected.
		if ( $hardcoded_strings > 5 ) {
			$issues[] = array(
				'type'        => 'hardcoded_strings',
				'description' => sprintf(
					/* translators: %d: number of hardcoded strings */
					__( '%d hardcoded strings detected; text cannot be translated', 'wpshadow' ),
					$hardcoded_strings
				),
			);
		}

		// Issue 5: No translation functions used.
		if ( ! $uses_translation_functions ) {
			$issues[] = array(
				'type'        => 'no_translation_functions',
				'description' => __( 'Theme does not use translation functions; strings not prepared for translation', 'wpshadow' ),
			);
		}

		// Issue 6: No RTL stylesheet for RTL languages.
		if ( ! $has_rtl_stylesheet ) {
			$issues[] = array(
				'type'        => 'no_rtl_stylesheet',
				'description' => __( 'No rtl.css file; site layout broken for Arabic/Hebrew languages', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site is not properly prepared for translation, limiting global reach and accessibility for non-English users', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/translation-readiness?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'theme_name'              => $theme_name,
					'text_domain'             => $text_domain,
					'has_languages_dir'       => $has_languages_dir,
					'pot_files'               => $pot_files,
					'po_files_count'          => count( $po_files ),
					'mo_files_count'          => count( $mo_files ),
					'loads_textdomain'        => $loads_textdomain,
					'uses_translation_functions' => $uses_translation_functions,
					'hardcoded_strings'       => $hardcoded_strings,
					'site_language'           => $site_language,
					'is_english_only'         => $is_english_only,
					'active_translation_plugins' => $active_translation_plugins,
					'has_rtl_stylesheet'      => $has_rtl_stylesheet,
					'has_language_switcher'   => $has_language_switcher,
					'has_glotpress'           => $has_glotpress,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Create /languages directory, generate .pot file, use translation functions, add RTL support', 'wpshadow' ),
					'translation_functions'   => array(
						'__( $text, $domain )'        => 'Retrieve translated text',
						'_e( $text, $domain )'        => 'Echo translated text',
						'esc_html__( $text, $domain )' => 'Retrieve and escape',
						'esc_html_e( $text, $domain )' => 'Echo and escape',
						'_n( $single, $plural, $n, $domain )' => 'Plural forms',
						'_x( $text, $context, $domain )' => 'Context-specific',
					),
					'load_textdomain_code'    => "load_theme_textdomain( '{$text_domain}', get_template_directory() . '/languages' );",
					'pot_generation_tools'    => array(
						'WP-CLI'   => 'wp i18n make-pot',
						'Poedit'   => 'Extract strings from source code',
						'Loco Translate' => 'WordPress plugin',
					),
					'global_market_stats'     => '60% of web users prefer content in their native language',
				),
			);
		}

		return null;
	}
}
