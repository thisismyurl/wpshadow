<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Related Content Block
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-related-content-block
 * Training: https://wpshadow.com/training/design-related-content-block
 */
class Diagnostic_Design_DESIGN_RELATED_CONTENT_BLOCK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-related-content-block',
            'title' => __('Related Content Block', 'wpshadow'),
            'description' => __('Checks related content blocks are styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-related-content-block',
            'training_link' => 'https://wpshadow.com/training/design-related-content-block',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}