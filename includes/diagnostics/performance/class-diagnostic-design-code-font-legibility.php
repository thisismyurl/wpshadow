<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Code Font Legibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-code-font-legibility
 * Training: https://wpshadow.com/training/design-code-font-legibility
 */
class Diagnostic_Design_CODE_FONT_LEGIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-code-font-legibility',
            'title' => __('Code Font Legibility', 'wpshadow'),
            'description' => __('Confirms code blocks use monospace font.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-code-font-legibility',
            'training_link' => 'https://wpshadow.com/training/design-code-font-legibility',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
