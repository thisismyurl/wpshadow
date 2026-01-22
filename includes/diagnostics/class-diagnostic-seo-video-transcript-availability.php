<?php declare(strict_types=1);
/**
 * Video Transcript Availability Diagnostic
 *
 * Philosophy: Transcripts make videos indexable
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Video_Transcript_Availability {
    public static function check() {
        return [
            'id' => 'seo-video-transcript-availability',
            'title' => 'Video Transcript Implementation',
            'description' => 'Provide full text transcripts for videos to make content searchable and accessible.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/video-transcripts/',
            'training_link' => 'https://wpshadow.com/training/video-seo/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}
