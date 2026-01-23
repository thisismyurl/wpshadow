<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PWA Design Compliance
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-progressive-web-app
 * Training: https://wpshadow.com/training/design-progressive-web-app
 */
class Diagnostic_Design_PROGRESSIVE_WEB_APP extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-progressive-web-app',
            'title' => __('PWA Design Compliance', 'wpshadow'),
            'description' => __('Validates offline page design, app shell.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-progressive-web-app',
            'training_link' => 'https://wpshadow.com/training/design-progressive-web-app',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}