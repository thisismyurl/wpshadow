<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Media Alt Fallback
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-media-alt-fallback
 * Training: https://wpshadow.com/training/design-block-media-alt-fallback
 */
class Diagnostic_Design_DESIGN_BLOCK_MEDIA_ALT_FALLBACK {
    public static function check() {
        return [
            'id' => 'design-block-media-alt-fallback',
            'title' => __('Block Media Alt Fallback', 'wpshadow'),
            'description' => __('Ensures media blocks enforce alt or fallback text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-media-alt-fallback',
            'training_link' => 'https://wpshadow.com/training/design-block-media-alt-fallback',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

