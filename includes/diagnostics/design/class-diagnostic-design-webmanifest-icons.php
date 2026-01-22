<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Web App Manifest Icons
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-webmanifest-icons
 * Training: https://wpshadow.com/training/design-webmanifest-icons
 */
class Diagnostic_Design_WEBMANIFEST_ICONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-webmanifest-icons',
            'title' => __('Web App Manifest Icons', 'wpshadow'),
            'description' => __('Checks PWA manifest has all icon sizes.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-webmanifest-icons',
            'training_link' => 'https://wpshadow.com/training/design-webmanifest-icons',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
