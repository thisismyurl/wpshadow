<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Breakpoint Redundancy
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-breakpoint-redundancy
 * Training: https://wpshadow.com/training/design-debt-breakpoint-redundancy
 */
class Diagnostic_Design_DEBT_BREAKPOINT_REDUNDANCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-breakpoint-redundancy',
            'title' => __('Breakpoint Redundancy', 'wpshadow'),
            'description' => __('Finds unused/redundant breakpoint definitions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-breakpoint-redundancy',
            'training_link' => 'https://wpshadow.com/training/design-debt-breakpoint-redundancy',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}