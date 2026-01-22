<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Template Presence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-template-presence
 * Training: https://wpshadow.com/training/design-template-presence
 */
class Diagnostic_Design_DESIGN_TEMPLATE_PRESENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-template-presence',
            'title' => __('Template Presence', 'wpshadow'),
            'description' => __('Checks required templates exist and are used correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-template-presence',
            'training_link' => 'https://wpshadow.com/training/design-template-presence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
