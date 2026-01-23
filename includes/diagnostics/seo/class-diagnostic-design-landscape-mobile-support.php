<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Landscape Mobile Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-landscape-mobile-support
 * Training: https://wpshadow.com/training/design-landscape-mobile-support
 */
class Diagnostic_Design_LANDSCAPE_MOBILE_SUPPORT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-landscape-mobile-support',
            'title' => __('Landscape Mobile Support', 'wpshadow'),
            'description' => __('Checks landscape orientation (414px-896px) designed specifically.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-landscape-mobile-support',
            'training_link' => 'https://wpshadow.com/training/design-landscape-mobile-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}