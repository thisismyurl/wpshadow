<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: JavaScript Execution Speed
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-javascript-execution-speed
 * Training: https://wpshadow.com/training/design-javascript-execution-speed
 */
class Diagnostic_Design_JAVASCRIPT_EXECUTION_SPEED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-javascript-execution-speed',
            'title' => __('JavaScript Execution Speed', 'wpshadow'),
            'description' => __('Validates critical JS <50ms.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-javascript-execution-speed',
            'training_link' => 'https://wpshadow.com/training/design-javascript-execution-speed',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}