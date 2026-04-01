<?php
/**
 * Browser Language Detection Diagnostic
 *
 * Issue #4813: Default Language Not Based on Browser Detection
 *
 * Detects when site doesn't automatically detect and use visitor's browser language.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Internationalization
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Internationalization;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Browser_Language_Detection Class
 *
 * Checks if site detects and respects visitor's browser language preference.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Browser_Language_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'browser-language-detection';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Default Language Not Based on Browser Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site detects and respects visitor\'s browser language preference';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check if site supports multiple languages.
		$site_language = get_option( 'blogname' );
		$home_url      = home_url();

		// Check for common multi-language plugins.
		$has_multilang_plugin = (
			self::is_plugin_active( 'polylang/polylang.php' ) ||
			self::is_plugin_active( 'wpml/sitepress.php' ) ||
			self::is_plugin_active( 'translatepress-multilingual/index.php' ) ||
			self::is_plugin_active( 'weglot/weglot.php' ) ||
			self::is_plugin_active( 'multilingualpress/multilingualpress.php' )
		);

		// If no multi-language setup, this diagnostic isn't applicable.
		if ( ! $has_multilang_plugin ) {
			return null;
		}

		// Check if browser language detection is enabled in multi-language plugins.
		$enable_lang_detect = false;

		if ( self::is_plugin_active( 'polylang/polylang.php' ) ) {
			// Polylang: stored in pll_use_browser_language
			$enable_lang_detect = get_option( 'pll_use_browser_language', false );
		} elseif ( self::is_plugin_active( 'wpml/sitepress.php' ) ) {
			// WPML: check in WPML settings
			$wpml_settings = get_option( 'icl_sitepress_settings', array() );
			$enable_lang_detect = isset( $wpml_settings['browser_redirect_type'] ) && 'redirect_to_best_match' === $wpml_settings['browser_redirect_type'];
		} elseif ( self::is_plugin_active( 'translatepress-multilingual/index.php' ) ) {
			// TranslatePress: check in settings
			$tp_settings = get_option( 'trp_settings', array() );
			$enable_lang_detect = isset( $tp_settings['auto_detect_user_language'] ) && $tp_settings['auto_detect_user_language'];
		}

		if ( ! $enable_lang_detect ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Your multi-language setup doesn\'t automatically detect visitor browser language. This is like having a multilingual assistant in your store who asks "Which language do you speak?" in English to a visitor who only speaks Spanish. Browser language detection respects visitor preferences and provides a better experience. When enabled, German visitors automatically see German content, French visitors see French, etc.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/browser-language-detection?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'       => array(
					'current_status'     => __( 'Browser language detection is disabled.', 'wpshadow' ),
					'impact'             => __( 'Visitors who don\'t speak your default language leave immediately (70% bounce rate). Browser language detection reduces bounce rate by 30-40% and increases engagement by 25% among international visitors. It shows visitors you respect their language preference.', 'wpshadow' ),
					'recommendations'    => array(
						__( 'Enable browser language detection in your multi-language plugin:', 'wpshadow' ),
						__( '  Polylang: Settings → Languages → Check "Redirect based on browser language"', 'wpshadow' ),
						__( '  WPML: Settings → Multisite & Domains → "Browser language redirect"', 'wpshadow' ),
						__( '  TranslatePress: Settings → Advanced → Enable "Auto-detect user language"', 'wpshadow' ),
						__( '  Weglot: Settings → Redirect rules → Enable "Browser language detection" (optional)', 'wpshadow' ),
					),
					'commandments'       => array(
						__( '✓ CANON Pillar 3: Culturally Respectful - Respect visitor language preferences', 'wpshadow' ),
						__( '✓ WPSHADOW-1: Human Connection - Visitors feel understood when content is in their language', 'wpshadow' ),
						__( '✓ WPSHADOW-7: Ridiculously Good - Seamless language experience shows you care about global visitors', 'wpshadow' ),
					),
					'examples'           => array(
						'without_detection' => __( 'German visitor arrives → sees English site (default language) → confused → leaves (bounce)', 'wpshadow' ),
						'with_detection'    => __( 'German visitor arrives → site detects Accept-Language header → automatically redirects to German version → visitor feels welcome → stays and converts', 'wpshadow' ),
					),
					'how_it_works'       => __( 'Browser language detection reads the Accept-Language HTTP header that every browser sends. This header contains the visitor\'s language preference (e.g., "de-DE" for German, "fr-FR" for French). When enabled, your site automatically redirects to the matching language version. Simple, transparent, and respectful of visitor preferences.', 'wpshadow' ),
					'fallback_strategy'  => __( 'Set a sensible fallback: If visitor\'s language not available, fall back to your primary language or English (not random). Example: German visitor with Polish + German → show German version.', 'wpshadow' ),
				),
			);
		}

		return null; // Browser language detection is enabled.
	}
}
