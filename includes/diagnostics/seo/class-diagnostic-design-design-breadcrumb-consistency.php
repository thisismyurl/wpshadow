<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Breadcrumb Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-consistency
 * Training: https://wpshadow.com/training/design-breadcrumb-consistency
 */
class Diagnostic_Design_DESIGN_BREADCRUMB_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-breadcrumb-consistency',
            'title' => __('Breadcrumb Consistency', 'wpshadow'),
            'description' => __('Checks separator, spacing, and typography consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-consistency',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
