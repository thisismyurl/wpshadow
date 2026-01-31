<?php
/**
 * Translatepress String Translation Performance Diagnostic
 *
 * Translatepress String Translation Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1151.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress String Translation Performance Diagnostic Class
 *
 * @since 1.1151.0000
 */
class Diagnostic_TranslatepressStringTranslationPerformance extends Diagnostic_Base {

	protected static $slug = 'translatepress-string-translation-performance';
	protected static $title = 'Translatepress String Translation Performance';
	protected static $description = 'Translatepress String Translation Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$settings = get_option( 'trp_settings', array() );

		// Check 1: Verify translation cache is enabled
		$cache_enabled = isset( $settings['cache'] ) ? (bool) $settings['cache'] : false;
		if ( ! $cache_enabled ) {
			$issues[] = 'TranslatePress cache not enabled';
		}

		// Check 2: Check for string translation mode
		$string_translation = isset( $settings['translation_mode'] ) ? $settings['translation_mode'] : '';
		if ( 'automatic' === $string_translation ) {
			$issues[] = 'Automatic translation enabled without performance tuning';
		}

		// Check 3: Verify database optimization
		$db_optimization = get_option( 'trp_database_optimized', 0 );
		if ( ! $db_optimization ) {
			$issues[] = 'Translation database optimization not enabled';
		}

		// Check 4: Check for string translation logging
		$logging_enabled = get_option( 'trp_log_translation_requests', 0 );
		if ( $logging_enabled ) {
			$issues[] = 'Translation request logging enabled (performance impact)';
		}

		// Check 5: Verify automatic language detection
		$auto_detect = isset( $settings['auto_detect_language'] ) ? (bool) $settings['auto_detect_language'] : false;
		if ( $auto_detect ) {
			$issues[] = 'Automatic language detection enabled (extra overhead)';
		}

		// Check 6: Check for translation editor caching
		$editor_cache = get_option( 'trp_translation_editor_cache', 0 );
		if ( ! $editor_cache ) {
			$issues[] = 'Translation editor cache not enabled';
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
					'Found %d TranslatePress performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-string-translation-performance',
			);
		}

		return null;
	}
}
