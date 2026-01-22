<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Noncritical CSS Deferrable
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-noncritical-css-deferrable
 * Training: https://wpshadow.com/training/design-noncritical-css-deferrable
 */
class Diagnostic_Design_DESIGN_NONCRITICAL_CSS_DEFERRABLE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-noncritical-css-deferrable',
            'title' => __('Noncritical CSS Deferrable', 'wpshadow'),
            'description' => __('Identifies CSS that is safe to defer or split.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-noncritical-css-deferrable',
            'training_link' => 'https://wpshadow.com/training/design-noncritical-css-deferrable',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
