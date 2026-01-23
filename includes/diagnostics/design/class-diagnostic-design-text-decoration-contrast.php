<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Text Decoration Contrast
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-text-decoration-contrast
 * Training: https://wpshadow.com/training/design-text-decoration-contrast
 */
class Diagnostic_Design_TEXT_DECORATION_CONTRAST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-text-decoration-contrast',
            'title' => __('Text Decoration Contrast', 'wpshadow'),
            'description' => __('Confirms underlines sufficient contrast.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-decoration-contrast',
            'training_link' => 'https://wpshadow.com/training/design-text-decoration-contrast',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}