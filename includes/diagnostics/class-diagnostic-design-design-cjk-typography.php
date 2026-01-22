<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CJK Typography
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-cjk-typography
 * Training: https://wpshadow.com/training/design-cjk-typography
 */
class Diagnostic_Design_DESIGN_CJK_TYPOGRAPHY {
    public static function check() {
        return [
            'id' => 'design-cjk-typography',
            'title' => __('CJK Typography', 'wpshadow'),
            'description' => __('Checks line-height and word-break for CJK content.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-cjk-typography',
            'training_link' => 'https://wpshadow.com/training/design-cjk-typography',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

