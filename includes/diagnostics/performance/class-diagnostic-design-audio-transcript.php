<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Audio Transcript Availability
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-audio-transcript
 * Training: https://wpshadow.com/training/design-audio-transcript
 */
class Diagnostic_Design_AUDIO_TRANSCRIPT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-audio-transcript',
            'title' => __('Audio Transcript Availability', 'wpshadow'),
            'description' => __('Validates podcasts, audio include transcript link.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-audio-transcript',
            'training_link' => 'https://wpshadow.com/training/design-audio-transcript',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
