<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Parallax Cost
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-parallax-cost
 * Training: https://wpshadow.com/training/design-parallax-cost
 */
class Diagnostic_Design_DESIGN_PARALLAX_COST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-parallax-cost',
            'title' => __('Parallax Cost', 'wpshadow'),
            'description' => __('Flags scroll-tied parallax without throttling.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-parallax-cost',
            'training_link' => 'https://wpshadow.com/training/design-parallax-cost',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
