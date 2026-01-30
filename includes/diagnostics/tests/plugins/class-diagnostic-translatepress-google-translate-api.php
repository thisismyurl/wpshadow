<?php
/**
 * Translatepress Google Translate Api Diagnostic
 *
 * Translatepress Google Translate Api misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1154.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Google Translate Api Diagnostic Class
 *
 * @since 1.1154.0000
 */
class Diagnostic_TranslatepressGoogleTranslateApi extends Diagnostic_Base {

	protected static $slug = 'translatepress-google-translate-api';
	protected static $title = 'Translatepress Google Translate Api';
	protected static $description = 'Translatepress Google Translate Api misconfigured';
	protected static $family = 'security';

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

		// Check if automatic translation is enabled
		$settings = get_option( 'trp_settings', array() );
		$machine_translation = isset( $settings['machine_translation'] ) ? $settings['machine_translation'] : 'no';
		
		if ( $machine_translation !== 'yes' ) {
			return null; // Automatic translation not enabled
		}

		// Check API key
		$api_key = isset( $settings['g-translate-key'] ) ? $settings['g-translate-key'] : '';
		if ( empty( $api_key ) ) {
			$issues[] = 'google_api_key_missing';
			$threat_level += 35;
		}

		// Check if API key is exposed in frontend
		if ( ! empty( $api_key ) ) {
			$restrict_api = isset( $settings['restrict_api_key'] ) ? $settings['restrict_api_key'] : 'no';
			if ( $restrict_api === 'no' ) {
				$issues[] = 'api_key_not_restricted';
				$threat_level += 30;
			}
		}

		// Check error logging
		$error_logging = isset( $settings['trp_advanced_settings']['enable_error_manager'] ) ? $settings['trp_advanced_settings']['enable_error_manager'] : 'no';
		if ( $error_logging === 'no' ) {
			$issues[] = 'translation_error_logging_disabled';
			$threat_level += 20;
		}

		// Check quota monitoring
		$quota_exceeded = get_transient( 'trp_machine_translation_quota_exceeded' );
		if ( $quota_exceeded ) {
			$issues[] = 'translation_quota_exceeded';
			$threat_level += 25;
		}

		// Check fallback configuration
		$use_deepl_fallback = isset( $settings['deepl-api-key'] ) && ! empty( $settings['deepl-api-key'] );
		if ( ! $use_deepl_fallback && ! empty( $api_key ) ) {
			$issues[] = 'no_translation_fallback_configured';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of API issues */
				__( 'TranslatePress Google Translate API has security and configuration issues: %s. This exposes API credentials and may cause translation failures.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-google-translate-api',
			);
		}
		
		return null;
	}
}
