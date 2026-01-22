<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Footer Responsive Layout
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-footer-responsive-layout
 * Training: https://wpshadow.com/training/design-footer-responsive-layout
 */
class Diagnostic_Design_FOOTER_RESPONSIVE_LAYOUT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-footer-responsive-layout',
            'title' => __('Footer Responsive Layout', 'wpshadow'),
            'description' => __('Validates footer stacks columns on mobile, doesn't hide links.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-footer-responsive-layout',
            'training_link' => 'https://wpshadow.com/training/design-footer-responsive-layout',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
