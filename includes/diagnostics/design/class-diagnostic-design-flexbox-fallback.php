<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Flexbox Fallback
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-flexbox-fallback
 * Training: https://wpshadow.com/training/design-flexbox-fallback
 */
class Diagnostic_Design_FLEXBOX_FALLBACK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-flexbox-fallback',
            'title' => __('Flexbox Fallback', 'wpshadow'),
            'description' => __('Validates Flexbox fallback for older browsers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-flexbox-fallback',
            'training_link' => 'https://wpshadow.com/training/design-flexbox-fallback',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}