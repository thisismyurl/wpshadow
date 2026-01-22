<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Network Inline Style Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-network-inline-style-bloat
 * Training: https://wpshadow.com/training/design-network-inline-style-bloat
 */
class Diagnostic_Design_DESIGN_NETWORK_INLINE_STYLE_BLOAT {
    public static function check() {
        return [
            'id' => 'design-network-inline-style-bloat',
            'title' => __('Network Inline Style Bloat', 'wpshadow'),
            'description' => __('Flags inline CSS variance across sites.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-network-inline-style-bloat',
            'training_link' => 'https://wpshadow.com/training/design-network-inline-style-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

