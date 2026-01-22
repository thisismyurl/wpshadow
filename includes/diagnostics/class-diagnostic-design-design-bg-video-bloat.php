<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Background Video Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-bg-video-bloat
 * Training: https://wpshadow.com/training/design-bg-video-bloat
 */
class Diagnostic_Design_DESIGN_BG_VIDEO_BLOAT {
    public static function check() {
        return [
            'id' => 'design-bg-video-bloat',
            'title' => __('Background Video Bloat', 'wpshadow'),
            'description' => __('Flags autoplay background videos without optimization or fallback.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-bg-video-bloat',
            'training_link' => 'https://wpshadow.com/training/design-bg-video-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

