<?php
/**
 * Weglot Javascript Translation Performance Diagnostic
 *
 * Weglot Javascript Translation Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1158.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weglot Javascript Translation Performance Diagnostic Class
 *
 * @since 1.1158.0000
 */
class Diagnostic_WeglotJavascriptTranslationPerformance extends Diagnostic_Base {

	protected static $slug = 'weglot-javascript-translation-performance';
	protected static $title = 'Weglot Javascript Translation Performance';
	protected static $description = 'Weglot Javascript Translation Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WEGLOT_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Async loading enabled
		$async = get_option( 'weglot_async_loading_enabled', 0 );
		if ( ! $async ) {
			$issues[] = 'Async loading not enabled';
		}

		// Check 2: Script optimization
		$script_opt = get_option( 'weglot_script_optimization_enabled', 0 );
		if ( ! $script_opt ) {
			$issues[] = 'Script optimization not enabled';
		}

		// Check 3: Translation caching
		$cache = get_option( 'weglot_translation_caching', 0 );
		if ( ! $cache ) {
			$issues[] = 'Translation caching not enabled';
		}

		// Check 4: Language detection optimization
		$lang_detect = get_option( 'weglot_language_detection_opt', 0 );
		if ( ! $lang_detect ) {
			$issues[] = 'Language detection optimization not enabled';
		}

		// Check 5: Lazy translation loading
		$lazy = get_option( 'weglot_lazy_translation_loading', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy translation loading not enabled';
		}

		// Check 6: Performance monitoring
		$monitoring = get_option( 'weglot_performance_monitoring', 0 );
		if ( ! $monitoring ) {
			$issues[] = 'Performance monitoring not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Weglot performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/weglot-javascript-translation-performance',
			);
		}

		return null;
	}
}
