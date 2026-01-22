<?php
declare(strict_types=1);
/**
 * Video Thumbnail Optimization Diagnostic
 *
 * Philosophy: Good thumbnails increase CTR
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Video_Thumbnail_Optimization extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-video-thumbnail-optimization',
            'title' => 'Video Thumbnail Quality',
            'description' => 'Use high-quality, descriptive video thumbnails (min 1280x720px) with thumbnailUrl in schema.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/video-thumbnails/',
            'training_link' => 'https://wpshadow.com/training/video-optimization/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
