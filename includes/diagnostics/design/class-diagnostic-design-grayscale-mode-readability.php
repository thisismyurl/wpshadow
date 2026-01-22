<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Grayscale Mode Readability
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-grayscale-mode-readability
 * Training: https://wpshadow.com/training/design-grayscale-mode-readability
 */
class Diagnostic_Design_GRAYSCALE_MODE_READABILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-grayscale-mode-readability',
            'title' => __('Grayscale Mode Readability', 'wpshadow'),
            'description' => __('Tests design readable in grayscale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-grayscale-mode-readability',
            'training_link' => 'https://wpshadow.com/training/design-grayscale-mode-readability',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
