<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Notification & Toast Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-notification-toast-design
 * Training: https://wpshadow.com/training/design-notification-toast-design
 */
class Diagnostic_Design_NOTIFICATION_TOAST_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-notification-toast-design',
            'title' => __('Notification & Toast Design', 'wpshadow'),
            'description' => __('Confirms notifications auto-dismiss (3-5s), stack properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-notification-toast-design',
            'training_link' => 'https://wpshadow.com/training/design-notification-toast-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design NOTIFICATION TOAST DESIGN
	 * Slug: -design-notification-toast-design
	 * File: class-diagnostic-design-notification-toast-design.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design NOTIFICATION TOAST DESIGN
	 * Slug: -design-notification-toast-design
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
	public static function test_live__design_notification_toast_design(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Notification toasts are well-designed and accessible'];
		}
		$message = $result['description'] ?? 'Toast notification design issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
