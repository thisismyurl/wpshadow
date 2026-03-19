<?php
/**
 * Site Language Configuration
 *
 * Checks if site language is appropriately configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Site_Language_Configuration Class
 *
 * Validates site language configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Site_Language_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-language-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Language Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates site language configuration and text direction';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests language configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$locale = get_locale();
		$language = get_option( 'WPLANG', '' );

		// Check 1: Language is not generic (en vs en_US)
		if ( $locale === 'en_US' || $locale === 'en' ) {
			// English is fine, but check if site is in different language region
			if ( defined( 'WP_LANG_DIR' ) && is_dir( WP_LANG_DIR ) ) {
				$installed_langs = glob( WP_LANG_DIR . '/*/LC_MESSAGES/wordpress.mo' );
				if ( ! empty( $installed_langs ) && count( $installed_langs ) > 1 ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'Multiple languages installed but English selected', 'wpshadow' ),
						'severity'     => 'low',
						'threat_level' => 20,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/site-language-setup',
						'recommendations' => array(
							__( 'Select appropriate site language from installed options', 'wpshadow' ),
							__( 'Ensure language matches site audience', 'wpshadow' ),
						),
					);
				}
			}
		}

		// Check 2: Language files are loaded
		if ( ! self::language_files_exist() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: locale */
					__( 'Language files not found for %s', 'wpshadow' ),
					$locale
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-language-files',
				'recommendations' => array(
					__( 'Download language files from WordPress.org', 'wpshadow' ),
					__( 'Place in wp-content/languages/ directory', 'wpshadow' ),
					__( 'Or use WordPress language installer', 'wpshadow' ),
				),
			);
		}

		// Check 3: RTL language detection
		if ( is_rtl() && ! self::has_rtl_support() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Right-to-left language detected but theme may not support RTL', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rtl-language-support',
				'recommendations' => array(
					__( 'Use RTL-compatible theme', 'wpshadow' ),
					__( 'Check theme has RTL stylesheet', 'wpshadow' ),
					__( 'Test layout in RTL languages', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if language files exist.
	 *
	 * @since 1.6093.1200
	 * @return bool True if files exist.
	 */
	private static function language_files_exist() {
		$locale = get_locale();

		if ( 'en_US' === $locale || 'en' === $locale ) {
			return true; // English is always available
		}

		// Check if language file exists
		$lang_dir = trailingslashit( WP_CONTENT_DIR ) . 'languages';
		$mofile = $lang_dir . '/wordpress-' . $locale . '.mo';

		if ( file_exists( $mofile ) ) {
			return true;
		}

		// Check theme translation
		$theme_lang_dir = get_theme_root() . '/' . get_template() . '/languages';
		if ( file_exists( $theme_lang_dir ) && is_dir( $theme_lang_dir ) ) {
			$theme_mofile = $theme_lang_dir . '/' . get_template() . '-' . $locale . '.mo';
			if ( file_exists( $theme_mofile ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for RTL support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if RTL supported.
	 */
	private static function has_rtl_support() {
		// Check if theme has RTL stylesheet
		$theme_root = get_theme_root();
		$theme_dir = $theme_root . '/' . get_template();

		if ( file_exists( $theme_dir . '/rtl.css' ) ) {
			return true;
		}

		// Check if theme declares RTL support
		$theme = wp_get_theme();
		if ( $theme->has_theme_feature( '_rtl' ) ) {
			return true;
		}

		// Check for manual RTL support
		if ( has_filter( 'wpshadow_rtl_support' ) ) {
			return true;
		}

		return false;
	}
}
