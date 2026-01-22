<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Resource Hints Strategy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-resource-hints
 * Training: https://wpshadow.com/training/design-resource-hints
 */
class Diagnostic_Design_RESOURCE_HINTS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-resource-hints',
            'title' => __('Resource Hints Strategy', 'wpshadow'),
            'description' => __('Checks preconnect, dns-prefetch used.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-resource-hints',
            'training_link' => 'https://wpshadow.com/training/design-resource-hints',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
