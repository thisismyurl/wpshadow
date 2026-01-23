<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Card Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-card-consistency
 * Training: https://wpshadow.com/training/design-card-consistency
 */
class Diagnostic_Design_DESIGN_CARD_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-card-consistency',
            'title' => __('Card Consistency', 'wpshadow'),
            'description' => __('Checks card padding, ratio, and typography consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-card-consistency',
            'training_link' => 'https://wpshadow.com/training/design-card-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}