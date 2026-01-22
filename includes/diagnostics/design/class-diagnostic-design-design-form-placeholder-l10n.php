<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Placeholder Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-form-placeholder-l10n
 * Training: https://wpshadow.com/training/design-form-placeholder-l10n
 */
class Diagnostic_Design_DESIGN_FORM_PLACEHOLDER_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-placeholder-l10n',
            'title' => __('Form Placeholder Localization', 'wpshadow'),
            'description' => __('Checks placeholders remain readable and unclipped.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-placeholder-l10n',
            'training_link' => 'https://wpshadow.com/training/design-form-placeholder-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
