<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Table Overflow Localization
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-table-overflow-l10n
 * Training: https://wpshadow.com/training/design-table-overflow-l10n
 */
class Diagnostic_Design_DESIGN_TABLE_OVERFLOW_L10N extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-table-overflow-l10n',
            'title' => __('Table Overflow Localization', 'wpshadow'),
            'description' => __('Checks tables under long localized strings for overflow.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-table-overflow-l10n',
            'training_link' => 'https://wpshadow.com/training/design-table-overflow-l10n',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}