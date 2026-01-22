<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT CTA Visibility
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-cta-visibility
 * Training: https://wpshadow.com/training/design-vrt-cta-visibility
 */
class Diagnostic_Design_DESIGN_VRT_CTA_VISIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-cta-visibility',
            'title' => __('VRT CTA Visibility', 'wpshadow'),
            'description' => __('Ensures CTA contrast and placement remain intact versus baseline.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-cta-visibility',
            'training_link' => 'https://wpshadow.com/training/design-vrt-cta-visibility',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
