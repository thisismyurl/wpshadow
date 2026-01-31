<?php
/**
 * Language Detection Accuracy Diagnostic
 *
 * Language Detection Accuracy misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1191.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language Detection Accuracy Diagnostic Class
 *
 * @since 1.1191.0000
 */
class Diagnostic_LanguageDetectionAccuracy extends Diagnostic_Base {

	protected static $slug = 'language-detection-accuracy';
	protected static $title = 'Language Detection Accuracy';
	protected static $description = 'Language Detection Accuracy misconfigured';
	protected static $family = 'functionality';

	public static function check() {
			// Check if any multilingual plugin with auto-detection is active
		$has_detection = ( defined( 'ICL_SITEPRESS_VERSION' ) && get_option( 'icl_language_negotiation_type', 1 ) > 1 ) || // WPML
						  ( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_the_languages' ) ) ||        // Polylang
						  ( defined( 'TRP_PLUGIN_VERSION' ) && get_option( 'trp_settings' ) ) ||               // TranslatePress
						  class_exists( 'GTranslate' );                                                        // GTranslate

		if ( ! $has_detection ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check WPML detection method
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$negotiation_type = get_option( 'icl_language_negotiation_type', 1 );
			if ( $negotiation_type === 1 ) {
				$issues[] = 'wpml_browser_detection_disabled';
				$threat_level += 20;
			}
		}

		// Check IP geolocation
		$using_geolocation = get_option( 'wpml_geo_detect_enabled', 0 ) || 
							 get_option( 'gtranslate_geo_enabled', 0 ) ||
							 get_option( 'pll_geo_enabled', 0 );
							 
		if ( ! $using_geolocation ) {
			$issues[] = 'ip_geolocation_not_enabled';
			$threat_level += 15;
		}

		// Check default fallback language
		$default_lang = get_option( 'WPLANG', 'en_US' );
		if ( empty( $default_lang ) ) {
			$issues[] = 'no_fallback_language_configured';
			$threat_level += 25;
		}

		// Check automatic redirect
		$auto_redirect = get_option( 'icl_automatic_redirect', 0 ) ||
						 get_option( 'gtranslate_auto_switch', 0 ) ||
						 get_option( 'trp_auto_redirect', 0 );
						 
		if ( $auto_redirect ) {
			$issues[] = 'automatic_redirect_may_confuse_users';
			$threat_level += 20;
		}

		// Check caching compatibility
		$cache_plugins_active = is_plugin_active( 'wp-super-cache/wp-cache.php' ) ||
								 is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
								 is_plugin_active( 'wp-rocket/wp-rocket.php' );
		if ( $cache_plugins_active && ! get_option( 'wpml_cache_compatible_mode', 0 ) ) {
			$issues[] = 'cache_incompatibility_detected';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of detection issues */
				__( 'Language detection has accuracy problems: %s. This shows users wrong language versions and reduces user experience.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/language-detection-accuracy',
			);
		}
		
		return null;
	}
}
