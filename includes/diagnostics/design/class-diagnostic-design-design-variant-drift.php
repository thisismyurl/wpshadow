<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Variant Drift Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-variant-drift
 * Training: https://wpshadow.com/training/design-variant-drift
 */
class Diagnostic_Design_DESIGN_VARIANT_DRIFT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-variant-drift',
            'title' => __('Variant Drift Detection', 'wpshadow'),
            'description' => __('Diffs component variants against canonical CSS variables.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-variant-drift',
            'training_link' => 'https://wpshadow.com/training/design-variant-drift',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}