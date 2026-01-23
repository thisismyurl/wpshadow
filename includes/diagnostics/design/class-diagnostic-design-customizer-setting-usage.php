<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Theme Customizer Compliance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-customizer-setting-usage
 * Training: https://wpshadow.com/training/design-customizer-setting-usage
 */
class Diagnostic_Design_CUSTOMIZER_SETTING_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-customizer-setting-usage',
            'title' => __('Theme Customizer Compliance', 'wpshadow'),
            'description' => __('Confirms theme customizer settings actually affect front-end.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-customizer-setting-usage',
            'training_link' => 'https://wpshadow.com/training/design-customizer-setting-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}