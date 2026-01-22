<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Frame Budget Risk
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-frame-budget-risk
 * Training: https://wpshadow.com/training/design-frame-budget-risk
 */
class Diagnostic_Design_DESIGN_FRAME_BUDGET_RISK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-frame-budget-risk',
            'title' => __('Frame Budget Risk', 'wpshadow'),
            'description' => __('Estimates main-thread time risk for animations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-frame-budget-risk',
            'training_link' => 'https://wpshadow.com/training/design-frame-budget-risk',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
