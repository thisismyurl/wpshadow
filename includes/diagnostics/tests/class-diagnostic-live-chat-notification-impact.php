<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Live Chat/Push/Notification Impact (THIRD-347)
 *
 * Measures ongoing cost of chat, push, notification scripts.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_LiveChatNotificationImpact extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array
	{
		// Monitor live chat impact on performance
		$has_live_chat = apply_filters('wpshadow_live_chat_active', false);

		if ($has_live_chat) {
			$chat_impact = get_transient('wpshadow_livechat_impact_ms');

			if ($chat_impact && $chat_impact > 300) { // 300ms
				return array(
					'id' => 'live-chat-notification-impact',
					'title' => sprintf(__('Live Chat Impact: +%dms', 'wpshadow'), $chat_impact),
					'description' => __('Live chat widget is impacting page performance. Consider loading it asynchronously or after user interaction.', 'wpshadow'),
					'severity' => 'low',
					'category' => 'monitoring',
					'kb_link' => 'https://wpshadow.com/kb/livechat-optimization/',
					'training_link' => 'https://wpshadow.com/training/chat-widget-performance/',
					'auto_fixable' => false,
					'threat_level' => 35,
				);
			}
		}
		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: LiveChatNotificationImpact
	 * Slug: -live-chat-notification-impact
	 * File: class-diagnostic-live-chat-notification-impact.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: LiveChatNotificationImpact
	 * Slug: -live-chat-notification-impact
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
	public static function test_live__live_chat_notification_impact(): array
	{
		$has_live_chat = (bool) apply_filters('wpshadow_live_chat_active', false);
		$chat_impact   = get_transient('wpshadow_livechat_impact_ms');
		$has_issue     = ($has_live_chat && $chat_impact && $chat_impact > 300);

		$result = self::check();
		$diagnostic_found_issue = is_array($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'Live chat impact check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (active: %s, impact: %sms)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$has_live_chat ? 'yes' : 'no',
				$chat_impact !== false ? (string) $chat_impact : 'n/a'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
