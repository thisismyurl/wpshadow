<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Cover LCP
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-cover-lcp
 * Training: https://wpshadow.com/training/design-block-cover-lcp
 */
class Diagnostic_Design_DESIGN_BLOCK_COVER_LCP extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-cover-lcp',
            'title' => __('Block Cover LCP', 'wpshadow'),
            'description' => __('Ensures cover/hero blocks are optimized for LCP with sizes and srcset.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-cover-lcp',
            'training_link' => 'https://wpshadow.com/training/design-block-cover-lcp',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}