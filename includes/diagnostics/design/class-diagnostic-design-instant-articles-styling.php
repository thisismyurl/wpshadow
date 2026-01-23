<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Instant Articles Format
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-instant-articles-styling
 * Training: https://wpshadow.com/training/design-instant-articles-styling
 */
class Diagnostic_Design_INSTANT_ARTICLES_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-instant-articles-styling',
            'title' => __('Instant Articles Format', 'wpshadow'),
            'description' => __('If using Facebook IA, validates styling.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-instant-articles-styling',
            'training_link' => 'https://wpshadow.com/training/design-instant-articles-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}