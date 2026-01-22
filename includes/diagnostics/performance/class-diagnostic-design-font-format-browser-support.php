<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Format Browser Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-format-browser-support
 * Training: https://wpshadow.com/training/design-font-format-browser-support
 */
class Diagnostic_Design_FONT_FORMAT_BROWSER_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-format-browser-support',
            'title' => __('Font Format Browser Support', 'wpshadow'),
            'description' => __('Validates font formats supported.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-format-browser-support',
            'training_link' => 'https://wpshadow.com/training/design-font-format-browser-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
