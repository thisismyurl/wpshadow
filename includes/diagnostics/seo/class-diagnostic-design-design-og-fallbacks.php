<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: OG Fallbacks
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-og-fallbacks
 * Training: https://wpshadow.com/training/design-og-fallbacks
 */
class Diagnostic_Design_DESIGN_OG_FALLBACKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-og-fallbacks',
            'title' => __('OG Fallbacks', 'wpshadow'),
            'description' => __('Checks fallback images for posts lacking featured images.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-og-fallbacks',
            'training_link' => 'https://wpshadow.com/training/design-og-fallbacks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
