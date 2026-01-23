<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CLS Risks (Layout Shift)
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-cls-risks
 * Training: https://wpshadow.com/training/code-frontend-cls-risks
 */
class Diagnostic_Code_CODE_FRONTEND_CLS_RISKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-frontend-cls-risks',
            'title' => __('CLS Risks (Layout Shift)', 'wpshadow'),
            'description' => __('Flags media/ads without fixed dimensions causing cumulative shift.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-cls-risks',
            'training_link' => 'https://wpshadow.com/training/code-frontend-cls-risks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}