<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Quote Block Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-quote-styling
 * Training: https://wpshadow.com/training/design-block-quote-styling
 */
class Diagnostic_Design_BLOCK_QUOTE_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-quote-styling',
            'title' => __('Quote Block Styling', 'wpshadow'),
            'description' => __('Confirms blockquotes styled distinctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-quote-styling',
            'training_link' => 'https://wpshadow.com/training/design-block-quote-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}