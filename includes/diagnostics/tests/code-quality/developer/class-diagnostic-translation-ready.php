<?php
/**
 * Translation Ready Diagnostic
 *
 * Checks if theme is properly marked for translation with text domain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translation Ready Diagnostic Class
 *
 * Verifies that the active theme is properly configured for translation
 * with correct text domain and .pot file.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Translation_Ready extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'translation-ready';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Translation Ready';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme is properly marked for translation with text domain';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the translation ready diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if translation issues detected, null otherwise.
	 */
	public static function check() {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$issues    = array();
		$warnings  = array();

		// Check for Text Domain header in style.css.
		$text_domain = $theme->get( 'TextDomain' );
		if ( empty( $text_domain ) ) {
			$issues[] = __( 'Missing "Text Domain" header in style.css', 'wpshadow' );
		}

		// Check for Domain Path header.
		$domain_path = $theme->get( 'DomainPath' );
		if ( empty( $domain_path ) ) {
			$warnings[] = __( 'Missing "Domain Path" header - recommended for translations', 'wpshadow' );
		}

		// Check for languages directory.
		$languages_dir = $theme_dir . '/languages';
		if ( ! is_dir( $languages_dir ) ) {
			$warnings[] = __( 'Missing /languages directory', 'wpshadow' );
		}

		// Check for .pot file (translation template).
		$pot_files = glob( $theme_dir . '/languages/*.pot' );
		if ( empty( $pot_files ) ) {
			$warnings[] = __( 'Missing .pot translation template file', 'wpshadow' );
		}

		// Check for load_theme_textdomain in functions.php.
		$functions_php = $theme_dir . '/functions.php';
		if ( file_exists( $functions_php ) ) {
			$functions_content = file_get_contents( $functions_php );
			if ( strpos( $functions_content, 'load_theme_textdomain' ) === false ) {
				$issues[] = __( 'functions.php not loading text domain with load_theme_textdomain()', 'wpshadow' );
			}
		}

		// Scan for hardcoded strings in theme files (sample check).
		$template_files = glob( $theme_dir . '/*.php' );
		$hardcoded_strings_found = false;
		
		if ( ! empty( $template_files ) ) {
			foreach ( array_slice( $template_files, 0, 5 ) as $file ) {
				$content = file_get_contents( $file );
				
				// Look for echo with literal strings (not translatable).
				if ( preg_match( '/echo\s+["\'](?!<\?php)[^"\']{10,}["\']/', $content ) ) {
					$hardcoded_strings_found = true;
					break;
				}
			}
		}

		if ( $hardcoded_strings_found ) {
			$warnings[] = __( 'Possible hardcoded strings detected - should use translation functions', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme is not translation-ready: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/translation-ready',
				'context'      => array(
					'theme_name' => $theme->get( 'Name' ),
					'text_domain' => $text_domain,
					'issues'     => $issues,
					'warnings'   => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme translation setup has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/translation-ready',
				'context'      => array(
					'theme_name'  => $theme->get( 'Name' ),
					'text_domain' => $text_domain,
					'warnings'    => $warnings,
				),
			);
		}

		return null; // Theme is properly translation-ready.
	}
}
