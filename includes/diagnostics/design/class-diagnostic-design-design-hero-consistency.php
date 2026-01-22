<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hero Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-hero-consistency
 * Training: https://wpshadow.com/training/design-hero-consistency
 */
class Diagnostic_Design_DESIGN_HERO_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-hero-consistency',
            'title' => __('Hero Consistency', 'wpshadow'),
            'description' => __('Checks hero patterns are consistent across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hero-consistency',
            'training_link' => 'https://wpshadow.com/training/design-hero-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
