<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG 2.1 AA Compliance Target
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-wcag-2-1-aa-compliance
 * Training: https://wpshadow.com/training/design-wcag-2-1-aa-compliance
 */
class Diagnostic_Design_WCAG_2_1_AA_COMPLIANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-wcag-2-1-aa-compliance',
            'title' => __('WCAG 2.1 AA Compliance Target', 'wpshadow'),
            'description' => __('Confirms design aimed at WCAG 2.1 AA standard.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-wcag-2-1-aa-compliance',
            'training_link' => 'https://wpshadow.com/training/design-wcag-2-1-aa-compliance',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}