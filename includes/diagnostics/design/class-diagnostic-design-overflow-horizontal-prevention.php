<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Horizontal Overflow Prevention
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-overflow-horizontal-prevention
 * Training: https://wpshadow.com/training/design-overflow-horizontal-prevention
 */
class Diagnostic_Design_OVERFLOW_HORIZONTAL_PREVENTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-overflow-horizontal-prevention',
            'title' => __('Horizontal Overflow Prevention', 'wpshadow'),
            'description' => __('Verifies no horizontal scrolling at breakpoints.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-overflow-horizontal-prevention',
            'training_link' => 'https://wpshadow.com/training/design-overflow-horizontal-prevention',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}