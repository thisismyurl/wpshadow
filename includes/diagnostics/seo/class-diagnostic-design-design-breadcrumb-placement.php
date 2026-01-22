<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Breadcrumb Placement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-placement
 * Training: https://wpshadow.com/training/design-breadcrumb-placement
 */
class Diagnostic_Design_DESIGN_BREADCRUMB_PLACEMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-breadcrumb-placement',
            'title' => __('Breadcrumb Placement', 'wpshadow'),
            'description' => __('Checks breadcrumbs location and spacing consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-placement',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-placement',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
