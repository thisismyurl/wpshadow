<?php declare(strict_types=1);
/**
 * Video Captions Quality Diagnostic
 *
 * Philosophy: Captions improve accessibility and SEO
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Video_Captions_Quality {
    public static function check() {
        return [
            'id' => 'seo-video-captions-quality',
            'title' => 'Video Caption Quality',
            'description' => 'Add accurate closed captions (not auto-generated) for better accessibility and indexing.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/video-captions/',
            'training_link' => 'https://wpshadow.com/training/accessible-video/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
