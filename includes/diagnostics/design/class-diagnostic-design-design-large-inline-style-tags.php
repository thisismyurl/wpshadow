<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Large Inline Style Tags
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-large-inline-style-tags
 * Training: https://wpshadow.com/training/design-large-inline-style-tags
 */
class Diagnostic_Design_DESIGN_LARGE_INLINE_STYLE_TAGS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-large-inline-style-tags',
            'title' => __('Large Inline Style Tags', 'wpshadow'),
            'description' => __('Detects large <style> blocks in head or body.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-large-inline-style-tags',
            'training_link' => 'https://wpshadow.com/training/design-large-inline-style-tags',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}