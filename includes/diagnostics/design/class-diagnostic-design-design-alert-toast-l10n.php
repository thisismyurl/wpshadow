<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Alert and Toast Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-alert-toast-l10n
 * Training: https://wpshadow.com/training/design-alert-toast-l10n
 */
class Diagnostic_Design_DESIGN_ALERT_TOAST_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-alert-toast-l10n',
            'title' => __('Alert and Toast Localization', 'wpshadow'),
            'description' => __('Checks alerts and toasts handle long or RTL text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-alert-toast-l10n',
            'training_link' => 'https://wpshadow.com/training/design-alert-toast-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}