<?php
declare(strict_types=1);
/**
 * Video Duration Metadata Diagnostic
 *
 * Philosophy: Duration helps users decide to watch
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Video_Duration_Metadata extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-video-duration-metadata',
            'title' => 'Video Duration in Schema',
            'description' => 'Include duration property in VideoObject schema for better video search results.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/video-schema/',
            'training_link' => 'https://wpshadow.com/training/video-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}