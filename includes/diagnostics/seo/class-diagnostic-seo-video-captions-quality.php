<?php
declare(strict_types=1);
/**
 * Video Captions Quality Diagnostic
 *
 * Philosophy: Captions improve accessibility and SEO
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Video_Captions_Quality extends Diagnostic_Base {
    public static function check(): ?array {
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
