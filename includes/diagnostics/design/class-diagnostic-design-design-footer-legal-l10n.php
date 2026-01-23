<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Footer Legal Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-footer-legal-l10n
 * Training: https://wpshadow.com/training/design-footer-legal-l10n
 */
class Diagnostic_Design_DESIGN_FOOTER_LEGAL_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-footer-legal-l10n',
            'title' => __('Footer Legal Localization', 'wpshadow'),
            'description' => __('Checks long legal text fits without overflow.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-footer-legal-l10n',
            'training_link' => 'https://wpshadow.com/training/design-footer-legal-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}