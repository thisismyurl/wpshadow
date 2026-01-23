<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Dynamic Content Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-dynamic-content-consistency
 * Training: https://wpshadow.com/training/design-dynamic-content-consistency
 */
class Diagnostic_Design_DYNAMIC_CONTENT_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-dynamic-content-consistency',
            'title' => __('Dynamic Content Consistency', 'wpshadow'),
            'description' => __('Checks dynamically-loaded content styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-dynamic-content-consistency',
            'training_link' => 'https://wpshadow.com/training/design-dynamic-content-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}