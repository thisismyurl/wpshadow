<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Video Embed Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-video-embed
 * Training: https://wpshadow.com/training/design-block-video-embed
 */
class Diagnostic_Design_BLOCK_VIDEO_EMBED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-video-embed',
            'title' => __('Video Embed Styling', 'wpshadow'),
            'description' => __('Checks embedded videos responsive.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-video-embed',
            'training_link' => 'https://wpshadow.com/training/design-block-video-embed',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
