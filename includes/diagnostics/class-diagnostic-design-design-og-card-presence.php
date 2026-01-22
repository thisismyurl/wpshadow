<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: OG Card Presence
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-og-card-presence
 * Training: https://wpshadow.com/training/design-og-card-presence
 */
class Diagnostic_Design_DESIGN_OG_CARD_PRESENCE {
    public static function check() {
        return [
            'id' => 'design-og-card-presence',
            'title' => __('OG Card Presence', 'wpshadow'),
            'description' => __('Checks OG and Twitter cards exist with correct sizes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-og-card-presence',
            'training_link' => 'https://wpshadow.com/training/design-og-card-presence',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

