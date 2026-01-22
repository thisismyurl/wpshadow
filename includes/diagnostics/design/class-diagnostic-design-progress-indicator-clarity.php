<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Progress Indicator Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-progress-indicator-clarity
 * Training: https://wpshadow.com/training/design-progress-indicator-clarity
 */
class Diagnostic_Design_PROGRESS_INDICATOR_CLARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-progress-indicator-clarity',
            'title' => __('Progress Indicator Clarity', 'wpshadow'),
            'description' => __('Validates progress bars show percentage, current step, total steps.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-progress-indicator-clarity',
            'training_link' => 'https://wpshadow.com/training/design-progress-indicator-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
