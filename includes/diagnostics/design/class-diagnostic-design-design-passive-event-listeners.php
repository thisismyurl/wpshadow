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

}