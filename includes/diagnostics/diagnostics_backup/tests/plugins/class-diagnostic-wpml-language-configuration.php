<?php
/**
 * WPML Language Configuration Diagnostic
 *
 * WPML language settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.298.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML Language Configuration Diagnostic Class
 *
 * @since 1.298.0000
 */
class Diagnostic_WpmlLanguageConfiguration extends Diagnostic_Base {

	protected static $slug = 'wpml-language-configuration';
	protected static $title = 'WPML Language Configuration';
	protected static $description = 'WPML language settings misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Active languages
		$active_languages = apply_filters( 'wpml_active_languages', null );
		if ( empty( $active_languages ) ) {
			$issues[] = __( 'No active languages (WPML not configured)', 'wpshadow' );
		} elseif ( count( $active_languages ) > 10 ) {
			$issues[] = sprintf( __( '%d languages (performance impact)', 'wpshadow' ), count( $active_languages ) );
		}

		// Check 2: Language switcher
		$switcher = get_option( 'wpml_language_switcher', 'none' );
		if ( 'none' === $switcher ) {
			$issues[] = __( 'No language switcher (users stuck in one language)', 'wpshadow' );
		}

		// Check 3: Translation method
		$method = get_option( 'wpml_translation_method', 'manual' );
		if ( 'manual' === $method ) {
			$issues[] = __( 'Manual translation only (slow workflow)', 'wpshadow' );
		}

		// Check 4: String translation
		global $wpdb;
		$untranslated = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}icl_strings WHERE status = %d",
				0
			)
		);
		if ( $untranslated > 100 ) {
			$issues[] = sprintf( __( '%d untranslated strings (incomplete)', 'wpshadow' ), $untranslated );
		}

		// Check 5: Media translation
		$media_translation = get_option( 'wpml_media_translation', 'no' );
		if ( 'no' === $media_translation ) {
			$issues[] = __( 'Media not translated (wrong images per language)', 'wpshadow' );
		}

		// Check 6: SEO settings
		$seo = get_option( 'wpml_seo_settings', array() );
		if ( empty( $seo ) ) {
			$issues[] = __( 'No SEO configuration (duplicate content)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WPML has %d language configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wpml-language-configuration',
		);
	}
}
