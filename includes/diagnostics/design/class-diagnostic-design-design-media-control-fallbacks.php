<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Media Control Fallbacks
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-media-control-fallbacks
 * Training: https://wpshadow.com/training/design-media-control-fallbacks
 */
class Diagnostic_Design_DESIGN_MEDIA_CONTROL_FALLBACKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-media-control-fallbacks',
            'title' => __('Media Control Fallbacks', 'wpshadow'),
            'description' => __('Checks fallbacks exist for missing or invalid media.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-media-control-fallbacks',
            'training_link' => 'https://wpshadow.com/training/design-media-control-fallbacks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
