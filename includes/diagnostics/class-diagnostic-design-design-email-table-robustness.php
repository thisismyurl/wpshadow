<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Email Table Robustness
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-email-table-robustness
 * Training: https://wpshadow.com/training/design-email-table-robustness
 */
class Diagnostic_Design_DESIGN_EMAIL_TABLE_ROBUSTNESS {
    public static function check() {
        return [
            'id' => 'design-email-table-robustness',
            'title' => __('Email Table Robustness', 'wpshadow'),
            'description' => __('Checks email table layouts for client robustness.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-email-table-robustness',
            'training_link' => 'https://wpshadow.com/training/design-email-table-robustness',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

