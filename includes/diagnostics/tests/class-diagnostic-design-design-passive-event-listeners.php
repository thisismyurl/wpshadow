<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Passive Event Listeners
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-passive-event-listeners
 * Training: https://wpshadow.com/training/design-passive-event-listeners
 */
class Diagnostic_Design_DESIGN_PASSIVE_EVENT_LISTENERS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-passive-event-listeners',
            'title' => __('Passive Event Listeners', 'wpshadow'),
            'description' => __('Ensures passive listeners for scroll and touch where safe.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-passive-event-listeners',
            'training_link' => 'https://wpshadow.com/training/design-passive-event-listeners',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN PASSIVE EVENT LISTENERS
	 * Slug: -design-design-passive-event-listeners
	 * File: class-diagnostic-design-design-passive-event-listeners.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN PASSIVE EVENT LISTENERS
	 * Slug: -design-design-passive-event-listeners
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
	public static function test_live__design_design_passive_event_listeners(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Event listeners properly use passive flag for performance'];
		}
		$message = $result['description'] ?? 'Event listener performance issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
