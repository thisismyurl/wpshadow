<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Animation Jank Risk
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-animation-jank-risk
 * Training: https://wpshadow.com/training/design-animation-jank-risk
 */
class Diagnostic_Design_DESIGN_ANIMATION_JANK_RISK {
    public static function check() {
        return [
            'id' => 'design-animation-jank-risk',
            'title' => __('Animation Jank Risk', 'wpshadow'),
            'description' => __('Flags heavy or unthrottled animations that may jank.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animation-jank-risk',
            'training_link' => 'https://wpshadow.com/training/design-animation-jank-risk',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

