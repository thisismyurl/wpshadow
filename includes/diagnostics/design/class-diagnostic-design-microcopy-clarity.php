<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Microcopy Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-microcopy-clarity
 * Training: https://wpshadow.com/training/design-microcopy-clarity
 */
class Diagnostic_Design_MICROCOPY_CLARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-microcopy-clarity',
            'title' => __('Microcopy Clarity', 'wpshadow'),
            'description' => __('Validates all small text clear, concise, non-technical.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-microcopy-clarity',
            'training_link' => 'https://wpshadow.com/training/design-microcopy-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}