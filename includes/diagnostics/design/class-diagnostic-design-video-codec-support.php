<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Video Codec Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-video-codec-support
 * Training: https://wpshadow.com/training/design-video-codec-support
 */
class Diagnostic_Design_VIDEO_CODEC_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-video-codec-support',
            'title' => __('Video Codec Support', 'wpshadow'),
            'description' => __('Confirms video codecs supported.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-video-codec-support',
            'training_link' => 'https://wpshadow.com/training/design-video-codec-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
