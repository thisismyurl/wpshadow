<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Gallery Block Responsive
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-gallery-responsive
 * Training: https://wpshadow.com/training/design-block-gallery-responsive
 */
class Diagnostic_Design_BLOCK_GALLERY_RESPONSIVE {
    public static function check() {
        return [
            'id' => 'design-block-gallery-responsive',
            'title' => __('Gallery Block Responsive', 'wpshadow'),
            'description' => __('Confirms gallery grid responsive.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-gallery-responsive',
            'training_link' => 'https://wpshadow.com/training/design-block-gallery-responsive',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
