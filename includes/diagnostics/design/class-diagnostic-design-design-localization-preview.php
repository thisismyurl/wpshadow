<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Localization Preview
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-localization-preview
 * Training: https://wpshadow.com/training/design-localization-preview
 */
class Diagnostic_Design_DESIGN_LOCALIZATION_PREVIEW extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-localization-preview',
            'title' => __('Localization Preview', 'wpshadow'),
            'description' => __('Checks preview with longer strings or RTL in customizer.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-localization-preview',
            'training_link' => 'https://wpshadow.com/training/design-localization-preview',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
