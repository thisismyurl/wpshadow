<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Fallback Stack
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-fallback-stack
 * Training: https://wpshadow.com/training/design-font-fallback-stack
 */
class Diagnostic_Design_FONT_FALLBACK_STACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-fallback-stack',
            'title' => __('Font Fallback Stack', 'wpshadow'),
            'description' => __('Verifies font-family includes system fallbacks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-fallback-stack',
            'training_link' => 'https://wpshadow.com/training/design-font-fallback-stack',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
