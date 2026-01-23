<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Call-to-Action Prominence
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-call-to-action-prominence
 * Training: https://wpshadow.com/training/design-call-to-action-prominence
 */
class Diagnostic_Design_CALL_TO_ACTION_PROMINENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-call-to-action-prominence',
            'title' => __('Call-to-Action Prominence', 'wpshadow'),
            'description' => __('Verifies primary CTA visually distinct, above fold.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-call-to-action-prominence',
            'training_link' => 'https://wpshadow.com/training/design-call-to-action-prominence',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}