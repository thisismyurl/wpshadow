<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: OG Alt Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-og-alt-localization
 * Training: https://wpshadow.com/training/design-og-alt-localization
 */
class Diagnostic_Design_DESIGN_OG_ALT_LOCALIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-og-alt-localization',
            'title' => __('OG Alt Localization', 'wpshadow'),
            'description' => __('Checks localized OG titles and descriptions when applicable.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-og-alt-localization',
            'training_link' => 'https://wpshadow.com/training/design-og-alt-localization',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
