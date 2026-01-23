<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Content Unused Classes
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-content-unused-classes
 * Training: https://wpshadow.com/training/design-content-unused-classes
 */
class Diagnostic_Design_DESIGN_CONTENT_UNUSED_CLASSES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-content-unused-classes',
            'title' => __('Content Unused Classes', 'wpshadow'),
            'description' => __('Detects CSS classes in post/page content that are never referenced by any stylesheet selectors.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-content-unused-classes',
            'training_link' => 'https://wpshadow.com/training/design-content-unused-classes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}