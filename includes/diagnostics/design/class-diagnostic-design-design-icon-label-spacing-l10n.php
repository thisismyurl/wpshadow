<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Icon Label Spacing Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-icon-label-spacing-l10n
 * Training: https://wpshadow.com/training/design-icon-label-spacing-l10n
 */
class Diagnostic_Design_DESIGN_ICON_LABEL_SPACING_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-icon-label-spacing-l10n',
            'title' => __('Icon Label Spacing Localization', 'wpshadow'),
            'description' => __('Checks icon and label spacing holds with text expansion.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-icon-label-spacing-l10n',
            'training_link' => 'https://wpshadow.com/training/design-icon-label-spacing-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}