<?php
declare(strict_types=1);
/**
 * Audio Content Transcription Diagnostic
 *
 * Philosophy: Audio transcripts improve findability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Audio_Content_Transcription extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-audio-content-transcription',
            'title' => 'Audio Content Transcription',
            'description' => 'Transcribe podcasts and audio content for searchability and accessibility.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/audio-transcripts/',
            'training_link' => 'https://wpshadow.com/training/podcast-seo/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
