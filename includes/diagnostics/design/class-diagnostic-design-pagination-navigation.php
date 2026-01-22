<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Pagination Navigation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-pagination-navigation
 * Training: https://wpshadow.com/training/design-pagination-navigation
 */
class Diagnostic_Design_PAGINATION_NAVIGATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-pagination-navigation',
            'title' => __('Pagination Navigation', 'wpshadow'),
            'description' => __('Validates pagination shows current page, nav buttons.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-pagination-navigation',
            'training_link' => 'https://wpshadow.com/training/design-pagination-navigation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
