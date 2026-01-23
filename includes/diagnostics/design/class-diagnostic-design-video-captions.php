<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Video Captions
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-video-captions
 * Training: https://wpshadow.com/training/design-video-captions
 */
class Diagnostic_Design_VIDEO_CAPTIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-video-captions',
            'title' => __('Video Captions', 'wpshadow'),
            'description' => __('Checks videos include captions for dialogue and sound effects.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-video-captions',
            'training_link' => 'https://wpshadow.com/training/design-video-captions',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}