<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Bot Categorization Accuracy and Cache Impact (SEC-PERF-340)
 *
 * Measures good vs bad bot detection and cache hit impact.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_BotCategorizationAccuracy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$bot_accuracy   = (float) get_transient( 'wpshadow_bot_detection_accuracy' ); // 0-100
		$bot_cache_miss = (float) get_transient( 'wpshadow_bot_cache_miss_rate' ); // percentage

		if ( ( $bot_accuracy > 0 && $bot_accuracy < 90 ) || $bot_cache_miss > 10 ) {
			return array(
				'id'              => 'bot-categorization-accuracy',
				'title'           => __( 'Bot categorization accuracy is low', 'wpshadow' ),
				'description'     => __( 'Bots are misclassified, hurting cache efficiency. Tune bot filters, update allow/deny lists, or use a CDN bot database.', 'wpshadow' ),
				'severity'        => 'medium',
				'category'        => 'other',
				'kb_link'         => 'https://wpshadow.com/kb/bot-categorization/',
				'training_link'   => 'https://wpshadow.com/training/bot-traffic-management/',
				'auto_fixable'    => false,
				'threat_level'    => 55,
				'accuracy'        => $bot_accuracy,
				'cache_miss_rate' => $bot_cache_miss,
			);
		}

		return null;
	}

}