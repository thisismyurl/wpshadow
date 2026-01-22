<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Text Alignment Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-text-alignment-alignment
 * Training: https://wpshadow.com/training/design-text-alignment-alignment
 */
class Diagnostic_Design_TEXT_ALIGNMENT_ALIGNMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-text-alignment-alignment',
            'title' => __('Text Alignment Consistency', 'wpshadow'),
            'description' => __('Checks body text left-aligned or right-aligned.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-alignment-alignment',
            'training_link' => 'https://wpshadow.com/training/design-text-alignment-alignment',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
