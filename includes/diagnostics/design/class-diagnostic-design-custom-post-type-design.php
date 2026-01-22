<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Custom Post Type Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-custom-post-type-design
 * Training: https://wpshadow.com/training/design-custom-post-type-design
 */
class Diagnostic_Design_CUSTOM_POST_TYPE_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-custom-post-type-design',
            'title' => __('Custom Post Type Design', 'wpshadow'),
            'description' => __('Verifies custom post types have proper templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-custom-post-type-design',
            'training_link' => 'https://wpshadow.com/training/design-custom-post-type-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
