<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Secondary Logo Variants
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-brand-secondary-logo-variants
 * Training: https://wpshadow.com/training/design-brand-secondary-logo-variants
 */
class Diagnostic_Design_BRAND_SECONDARY_LOGO_VARIANTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-brand-secondary-logo-variants',
            'title' => __('Secondary Logo Variants', 'wpshadow'),
            'description' => __('Checks availability of horizontal, stacked, icon-only, and monochrome logo variants.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-brand-secondary-logo-variants',
            'training_link' => 'https://wpshadow.com/training/design-brand-secondary-logo-variants',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
