<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Card Title Wrap
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-card-title-wrap
 * Training: https://wpshadow.com/training/design-card-title-wrap
 */
class Diagnostic_Design_DESIGN_CARD_TITLE_WRAP {
    public static function check() {
        return [
            'id' => 'design-card-title-wrap',
            'title' => __('Card Title Wrap', 'wpshadow'),
            'description' => __('Checks card titles wrap without breaking layout.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-card-title-wrap',
            'training_link' => 'https://wpshadow.com/training/design-card-title-wrap',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

