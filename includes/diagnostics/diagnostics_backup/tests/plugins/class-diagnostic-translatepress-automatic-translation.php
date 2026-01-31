<?php
/**
 * TranslatePress Automatic Translation Diagnostic
 *
 * TranslatePress API limits exceeded.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.313.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress Automatic Translation Diagnostic Class
 *
 * @since 1.313.0000
 */
class Diagnostic_TranslatepressAutomaticTranslation extends Diagnostic_Base {

	protected static $slug = 'translatepress-automatic-translation';
	protected static $title = 'TranslatePress Automatic Translation';
	protected static $description = 'TranslatePress API limits exceeded';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) && ! get_option( 'trp_settings', array() ) ) {
			return null;
		}

		$issues = array();
		$settings = get_option( 'trp_settings', array() );

		// Check 1: Automatic translation enabled
		$auto_translation = isset( $settings['automatic_translation'] ) ? (bool) $settings['automatic_translation'] : false;
		if ( ! $auto_translation ) {
			$issues[] = 'Automatic translation not enabled';
		}

		// Check 2: API key configured
		$api_key = isset( $settings['automatic_translation_api_key'] ) ? $settings['automatic_translation_api_key'] : '';
		if ( empty( $api_key ) ) {
			$issues[] = 'Automatic translation API key missing';
		}

		// Check 3: Daily quota configured
		$daily_quota = isset( $settings['automatic_translation_daily_quota'] ) ? absint( $settings['automatic_translation_daily_quota'] ) : 0;
		if ( $daily_quota <= 0 ) {
			$issues[] = 'Daily translation quota not configured';
		}

		// Check 4: Translation caching enabled
		$cache_enabled = isset( $settings['automatic_translation_cache'] ) ? (bool) $settings['automatic_translation_cache'] : false;
		if ( ! $cache_enabled ) {
			$issues[] = 'Translation cache not enabled';
		}

		// Check 5: Batch translation enabled
		$batch_enabled = isset( $settings['automatic_translation_batch'] ) ? (bool) $settings['automatic_translation_batch'] : false;
		if ( ! $batch_enabled ) {
			$issues[] = 'Batch translation not enabled';
		}

		// Check 6: Auto-translate strings
		$strings_auto = isset( $settings['automatic_translation_strings'] ) ? (bool) $settings['automatic_translation_strings'] : false;
		if ( ! $strings_auto ) {
			$issues[] = 'Automatic translation for strings not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d TranslatePress automatic translation issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-automatic-translation',
			);
		}

		return null;
	}
}
