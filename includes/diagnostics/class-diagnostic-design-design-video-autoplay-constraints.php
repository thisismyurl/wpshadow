<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Video Autoplay Constraints
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-video-autoplay-constraints
 * Training: https://wpshadow.com/training/design-video-autoplay-constraints
 */
class Diagnostic_Design_DESIGN_VIDEO_AUTOPLAY_CONSTRAINTS {
    public static function check() {
        return [
            'id' => 'design-video-autoplay-constraints',
            'title' => __('Video Autoplay Constraints', 'wpshadow'),
            'description' => __('Checks autoplay respects muted/playsinline and CPU budgets.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-video-autoplay-constraints',
            'training_link' => 'https://wpshadow.com/training/design-video-autoplay-constraints',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

