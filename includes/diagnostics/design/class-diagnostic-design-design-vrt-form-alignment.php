<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Form Alignment
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-form-alignment
 * Training: https://wpshadow.com/training/design-vrt-form-alignment
 */
class Diagnostic_Design_DESIGN_VRT_FORM_ALIGNMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-form-alignment',
            'title' => __('VRT Form Alignment', 'wpshadow'),
            'description' => __('Detects alignment regressions for inputs, labels, and errors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-form-alignment',
            'training_link' => 'https://wpshadow.com/training/design-vrt-form-alignment',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}