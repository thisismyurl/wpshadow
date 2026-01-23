<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Important Overuse
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-important-overuse
 * Training: https://wpshadow.com/training/design-important-overuse
 */
class Diagnostic_Design_DESIGN_IMPORTANT_OVERUSE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-important-overuse',
            'title' => __('Important Overuse', 'wpshadow'),
            'description' => __('Flags density of !important declarations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-important-overuse',
            'training_link' => 'https://wpshadow.com/training/design-important-overuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}