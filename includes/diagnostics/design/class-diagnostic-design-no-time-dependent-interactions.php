<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: No Time-Dependent Interactions
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-no-time-dependent-interactions
 * Training: https://wpshadow.com/training/design-no-time-dependent-interactions
 */
class Diagnostic_Design_NO_TIME_DEPENDENT_INTERACTIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-no-time-dependent-interactions',
            'title' => __('No Time-Dependent Interactions', 'wpshadow'),
            'description' => __('Verifies no session timeouts without control.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-no-time-dependent-interactions',
            'training_link' => 'https://wpshadow.com/training/design-no-time-dependent-interactions',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}