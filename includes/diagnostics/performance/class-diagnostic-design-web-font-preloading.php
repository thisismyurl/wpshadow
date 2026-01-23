<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Web Font Preloading
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-web-font-preloading
 * Training: https://wpshadow.com/training/design-web-font-preloading
 */
class Diagnostic_Design_WEB_FONT_PRELOADING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-web-font-preloading',
            'title' => __('Web Font Preloading', 'wpshadow'),
            'description' => __('Verifies critical fonts preloaded.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-web-font-preloading',
            'training_link' => 'https://wpshadow.com/training/design-web-font-preloading',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}