<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Logo and Favicon Sizing
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-logo-favicon-sizing
 * Training: https://wpshadow.com/training/design-logo-favicon-sizing
 */
class Diagnostic_Design_DESIGN_LOGO_FAVICON_SIZING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-logo-favicon-sizing',
            'title' => __('Logo and Favicon Sizing', 'wpshadow'),
            'description' => __('Checks logo, favicon, and header images are sized responsively.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-logo-favicon-sizing',
            'training_link' => 'https://wpshadow.com/training/design-logo-favicon-sizing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
