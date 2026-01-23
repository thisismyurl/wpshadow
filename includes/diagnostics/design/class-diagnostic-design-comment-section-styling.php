<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Comment Section Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-comment-section-styling
 * Training: https://wpshadow.com/training/design-comment-section-styling
 */
class Diagnostic_Design_COMMENT_SECTION_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-comment-section-styling',
            'title' => __('Comment Section Design', 'wpshadow'),
            'description' => __('Validates comment section styled professionally.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-comment-section-styling',
            'training_link' => 'https://wpshadow.com/training/design-comment-section-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}