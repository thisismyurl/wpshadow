<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Search_Intent_Alignment extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'monitor-intent-alignment',
			'title'         => __( 'Search Intent Alignment Monitoring', 'wpshadow' ),
			'description'   => __( 'Detects if page intent matches search query intent. Misalignment = low CTR, high bounce, ranking drop.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/search-intent/',
			'training_link' => 'https://wpshadow.com/training/intent-matching/',
			'auto_fixable'  => false,
			'threat_level'  => 7,
		); }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Search Intent Alignment
	 * Slug: -monitor-search-intent-alignment
	 * File: class-diagnostic-monitor-search-intent-alignment.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Search Intent Alignment
	 * Slug: -monitor-search-intent-alignment
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__monitor_search_intent_alignment(): array {
		$result = self::check();
		if ( $result === null ) {
			return array(
				'passed'  => true,
				'message' => 'Search results align well with user intent',
			);
		}
		$message = $result['description'] ?? 'Search intent alignment issue detected';
		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
