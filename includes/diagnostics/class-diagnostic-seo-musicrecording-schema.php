<?php declare(strict_types=1);
/**
 * MusicRecording Schema Diagnostic
 *
 * Philosophy: Music schema for audio content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_MusicRecording_Schema {
    public static function check() {
        return [
            'id' => 'seo-musicrecording-schema',
            'title' => 'MusicRecording Schema Markup',
            'description' => 'Add MusicRecording schema for music content: artist, album, duration, ISRC.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/music-schema/',
            'training_link' => 'https://wpshadow.com/training/music-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
