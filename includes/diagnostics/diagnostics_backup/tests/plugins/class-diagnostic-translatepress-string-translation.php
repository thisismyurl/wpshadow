<?php
/**
 * TranslatePress String Translation Diagnostic
 *
 * TranslatePress strings not translatable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.318.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress String Translation Diagnostic Class
 *
 * @since 1.318.0000
 */
class Diagnostic_TranslatepressStringTranslation extends Diagnostic_Base {

	protected static $slug = 'translatepress-string-translation';
	protected static $title = 'TranslatePress String Translation';
	protected static $description = 'TranslatePress strings not translatable';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		// Check if TranslatePress is active
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) && ! class_exists( 'TRP_Translate_Press' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check translation tables
		$settings = get_option( 'trp_settings', array() );
		$languages = isset( $settings['publish-languages'] ) ? $settings['publish-languages'] : array();
		
		if ( empty( $languages ) ) {
			return null;
		}

		// Check untranslated strings
		foreach ( $languages as $lang_code ) {
			$table_name = $wpdb->prefix . 'trp_dictionary_' . strtolower( str_replace( '-', '_', $lang_code ) );
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
			
			if ( $table_exists ) {
				$untranslated = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$table_name} WHERE status = 0"
				);
				if ( $untranslated > 100 ) {
					$issues[] = 'excessive_untranslated_strings';
					$threat_level += 20;
					break;
				}
			}
		}

		// Check automatic string detection
		$auto_detect = isset( $settings['trp_advanced_settings']['enable_auto_detect_strings'] ) ? $settings['trp_advanced_settings']['enable_auto_detect_strings'] : 'yes';
		if ( $auto_detect === 'no' ) {
			$issues[] = 'automatic_string_detection_disabled';
			$threat_level += 20;
		}

		// Check gettext integration
		$gettext = isset( $settings['trp_advanced_settings']['enable_gettext'] ) ? $settings['trp_advanced_settings']['enable_gettext'] : 'no';
		if ( $gettext === 'no' ) {
			$issues[] = 'gettext_integration_disabled';
			$threat_level += 15;
		}

		// Check translation blocks
		$translation_blocks = isset( $settings['translation-blocks'] ) ? $settings['translation-blocks'] : array();
		if ( empty( $translation_blocks ) ) {
			$issues[] = 'no_translation_blocks_configured';
			$threat_level += 10;
		}

		// Check string sanitization
		$sanitize_strings = isset( $settings['trp_advanced_settings']['sanitize_strings'] ) ? $settings['trp_advanced_settings']['sanitize_strings'] : 'yes';
		if ( $sanitize_strings === 'no' ) {
			$issues[] = 'string_sanitization_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of translation issues */
				__( 'TranslatePress string translation has problems: %s. This leaves content untranslated and reduces multilingual coverage.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-string-translation',
			);
		}
		
		return null;
	}
}
