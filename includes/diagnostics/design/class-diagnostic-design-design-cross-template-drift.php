<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cross-Template Drift Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-cross-template-drift
 * Training: https://wpshadow.com/training/design-cross-template-drift
 */
class Diagnostic_Design_DESIGN_CROSS_TEMPLATE_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-cross-template-drift',
            'title' => __('Cross-Template Drift Score', 'wpshadow'),
            'description' => __('Computes drift scores between key templates such as home, single, and archive.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-cross-template-drift',
            'training_link' => 'https://wpshadow.com/training/design-cross-template-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
