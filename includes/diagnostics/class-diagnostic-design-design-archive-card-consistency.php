<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Archive Card Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-archive-card-consistency
 * Training: https://wpshadow.com/training/design-archive-card-consistency
 */
class Diagnostic_Design_DESIGN_ARCHIVE_CARD_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-archive-card-consistency',
            'title' => __('Archive Card Consistency', 'wpshadow'),
            'description' => __('Checks archive cards are consistent with single cards.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-archive-card-consistency',
            'training_link' => 'https://wpshadow.com/training/design-archive-card-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

