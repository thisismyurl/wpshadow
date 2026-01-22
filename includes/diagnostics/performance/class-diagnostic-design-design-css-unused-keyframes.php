<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused CSS Keyframes
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-css-unused-keyframes
 * Training: https://wpshadow.com/training/design-css-unused-keyframes
 */
class Diagnostic_Design_DESIGN_CSS_UNUSED_KEYFRAMES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-unused-keyframes',
            'title' => __('Unused CSS Keyframes', 'wpshadow'),
            'description' => __('Detects keyframe animations that are never referenced in styles.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-unused-keyframes',
            'training_link' => 'https://wpshadow.com/training/design-css-unused-keyframes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
