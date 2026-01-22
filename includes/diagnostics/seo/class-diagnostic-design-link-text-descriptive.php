<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Link Text Descriptive
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-link-text-descriptive
 * Training: https://wpshadow.com/training/design-link-text-descriptive
 */
class Diagnostic_Design_LINK_TEXT_DESCRIPTIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-link-text-descriptive',
            'title' => __('Link Text Descriptive', 'wpshadow'),
            'description' => __('Checks link text meaningful (not 'Click here').', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-link-text-descriptive',
            'training_link' => 'https://wpshadow.com/training/design-link-text-descriptive',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
