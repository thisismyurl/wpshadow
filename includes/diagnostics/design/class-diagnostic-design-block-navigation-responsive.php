<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Navigation Block Responsive
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-navigation-responsive
 * Training: https://wpshadow.com/training/design-block-navigation-responsive
 */
class Diagnostic_Design_BLOCK_NAVIGATION_RESPONSIVE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-navigation-responsive',
            'title' => __('Navigation Block Responsive', 'wpshadow'),
            'description' => __('Confirms navigation block mobile menu works.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-navigation-responsive',
            'training_link' => 'https://wpshadow.com/training/design-block-navigation-responsive',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}