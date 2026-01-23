<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Noncompressed CSS
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-noncompressed-css
 * Training: https://wpshadow.com/training/design-noncompressed-css
 */
class Diagnostic_Design_DESIGN_NONCOMPRESSED_CSS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-noncompressed-css',
            'title' => __('Noncompressed CSS', 'wpshadow'),
            'description' => __('Flags CSS that is not minified or compressed in production.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-noncompressed-css',
            'training_link' => 'https://wpshadow.com/training/design-noncompressed-css',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}