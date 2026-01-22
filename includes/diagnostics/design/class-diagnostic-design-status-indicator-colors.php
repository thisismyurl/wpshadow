<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Status Indicator Colors
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-status-indicator-colors
 * Training: https://wpshadow.com/training/design-status-indicator-colors
 */
class Diagnostic_Design_STATUS_INDICATOR_COLORS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-status-indicator-colors',
            'title' => __('Status Indicator Colors', 'wpshadow'),
            'description' => __('Confirms status uses color + icon.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-status-indicator-colors',
            'training_link' => 'https://wpshadow.com/training/design-status-indicator-colors',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
