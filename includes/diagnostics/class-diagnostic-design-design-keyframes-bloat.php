<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Keyframes Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-keyframes-bloat
 * Training: https://wpshadow.com/training/design-keyframes-bloat
 */
class Diagnostic_Design_DESIGN_KEYFRAMES_BLOAT {
    public static function check() {
        return [
            'id' => 'design-keyframes-bloat',
            'title' => __('Keyframes Bloat', 'wpshadow'),
            'description' => __('Detects unused or oversized keyframes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-keyframes-bloat',
            'training_link' => 'https://wpshadow.com/training/design-keyframes-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

