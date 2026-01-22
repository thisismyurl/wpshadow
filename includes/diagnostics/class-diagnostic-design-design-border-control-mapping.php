<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Border Control Mapping
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-border-control-mapping
 * Training: https://wpshadow.com/training/design-border-control-mapping
 */
class Diagnostic_Design_DESIGN_BORDER_CONTROL_MAPPING {
    public static function check() {
        return [
            'id' => 'design-border-control-mapping',
            'title' => __('Border Control Mapping', 'wpshadow'),
            'description' => __('Checks borders map to tokenized widths and styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-border-control-mapping',
            'training_link' => 'https://wpshadow.com/training/design-border-control-mapping',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

