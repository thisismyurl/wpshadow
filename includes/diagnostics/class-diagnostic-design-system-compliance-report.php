<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Design System Compliance Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-compliance-report
 * Training: https://wpshadow.com/training/design-system-compliance-report
 */
class Diagnostic_Design_SYSTEM_COMPLIANCE_REPORT {
    public static function check() {
        return [
            'id' => 'design-system-compliance-report',
            'title' => __('Design System Compliance Score', 'wpshadow'),
            'description' => __('Overall compliance percentage with defined system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-compliance-report',
            'training_link' => 'https://wpshadow.com/training/design-system-compliance-report',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
