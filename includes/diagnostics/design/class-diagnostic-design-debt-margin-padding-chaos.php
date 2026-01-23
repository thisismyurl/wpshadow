<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Margin/Padding Chaos
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-margin-padding-chaos
 * Training: https://wpshadow.com/training/design-debt-margin-padding-chaos
 */
class Diagnostic_Design_DEBT_MARGIN_PADDING_CHAOS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-margin-padding-chaos',
            'title' => __('Margin/Padding Chaos', 'wpshadow'),
            'description' => __('Quantifies inconsistent spacing (11 different margin values).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-margin-padding-chaos',
            'training_link' => 'https://wpshadow.com/training/design-debt-margin-padding-chaos',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}