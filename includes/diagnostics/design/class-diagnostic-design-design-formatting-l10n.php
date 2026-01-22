<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Formatting Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-formatting-l10n
 * Training: https://wpshadow.com/training/design-formatting-l10n
 */
class Diagnostic_Design_DESIGN_FORMATTING_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-formatting-l10n',
            'title' => __('Formatting Localization', 'wpshadow'),
            'description' => __('Checks date, number, and currency formatting spacing/alignment.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-formatting-l10n',
            'training_link' => 'https://wpshadow.com/training/design-formatting-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
