<?php
/**
 * Translatepress Automatic Translation Quota Diagnostic
 *
 * Translatepress Automatic Translation Quota misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1150.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Automatic Translation Quota Diagnostic Class
 *
 * @since 1.1150.0000
 */
class Diagnostic_TranslatepressAutomaticTranslationQuota extends Diagnostic_Base {

	protected static $slug = 'translatepress-automatic-translation-quota';
	protected static $title = 'Translatepress Automatic Translation Quota';
	protected static $description = 'Translatepress Automatic Translation Quota misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key configured
		$settings = get_option( 'trp_settings', array() );
		$api_key = $settings['g_translate_key'] ?? '';
		
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Google Translate API key not configured', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'TranslatePress automatic translation not configured', 'wpshadow' ),
				'severity'    => 60,
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-automatic-translation-quota',
			);
		}
		
		// Check 2: Translation service
		$translation_engine = $settings['translation_engine'] ?? 'google_translate_v2';
		if ( 'google_translate_v2' === $translation_engine ) {
			$issues[] = __( 'Using Google Translate v2 (deprecated, use v3)', 'wpshadow' );
		}
		
		// Check 3: Automatic translation mode
		$auto_translate = $settings['trp_advanced_settings']['automatically_translate_slug'] ?? 'no';
		if ( 'yes' === $auto_translate ) {
			$issues[] = __( 'Auto-translating slugs (quota intensive)', 'wpshadow' );
		}
		
		// Check 4: Translation memory
		$translation_memory = get_option( 'trp_translation_memory', array() );
		if ( count( $translation_memory ) > 100000 ) {
			$issues[] = sprintf( __( '%s translations in memory (database bloat)', 'wpshadow' ), number_format_i18n( count( $translation_memory ) ) );
		}
		
		// Check 5: Quota monitoring
		$quota_exceeded = get_transient( 'trp_quota_exceeded' );
		if ( $quota_exceeded ) {
			$issues[] = __( 'Translation quota exceeded (service unavailable)', 'wpshadow' );
		}
		
		// Check 6: Block crawlers
		$block_crawlers = $settings['trp_advanced_settings']['block_crawlers'] ?? 'no';
		if ( 'no' === $block_crawlers ) {
			$issues[] = __( 'Crawlers not blocked (quota waste)', 'wpshadow' );
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
				/* translators: %s: list of translation quota issues */
				__( 'TranslatePress has %d quota issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/translatepress-automatic-translation-quota',
		);
	}
}
