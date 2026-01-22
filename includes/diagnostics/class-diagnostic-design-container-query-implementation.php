<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Container Query Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-container-query-implementation
 * Training: https://wpshadow.com/training/design-container-query-implementation
 */
class Diagnostic_Design_CONTAINER_QUERY_IMPLEMENTATION {
    public static function check() {
        return [
            'id' => 'design-container-query-implementation',
            'title' => __('Container Query Implementation', 'wpshadow'),
            'description' => __('Checks if container queries used for responsive components.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-container-query-implementation',
            'training_link' => 'https://wpshadow.com/training/design-container-query-implementation',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
