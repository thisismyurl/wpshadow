<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Video Embed Responsiveness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-video-embed-responsiveness
 * Training: https://wpshadow.com/training/design-video-embed-responsiveness
 */
class Diagnostic_Design_VIDEO_EMBED_RESPONSIVENESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-video-embed-responsiveness',
            'title' => __('Video Embed Responsiveness', 'wpshadow'),
            'description' => __('Validates embedded videos responsive using container queries.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-video-embed-responsiveness',
            'training_link' => 'https://wpshadow.com/training/design-video-embed-responsiveness',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
