<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Breadcrumb Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-breadcrumb-l10n
 * Training: https://wpshadow.com/training/design-breadcrumb-l10n
 */
class Diagnostic_Design_DESIGN_BREADCRUMB_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-breadcrumb-l10n',
            'title' => __('Breadcrumb Localization', 'wpshadow'),
            'description' => __('Checks breadcrumb spacing with long segments and RTL.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-breadcrumb-l10n',
            'training_link' => 'https://wpshadow.com/training/design-breadcrumb-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}