<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pagination Styling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-pagination-styling
 * Training: https://wpshadow.com/training/design-pagination-styling
 */
class Diagnostic_Design_PAGINATION_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-pagination-styling',
            'title' => __('Pagination Styling', 'wpshadow'),
            'description' => __('Checks pagination styling consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-pagination-styling',
            'training_link' => 'https://wpshadow.com/training/design-pagination-styling',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
