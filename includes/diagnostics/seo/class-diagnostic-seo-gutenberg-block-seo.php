<?php
declare(strict_types=1);
/**
 * Gutenberg Block SEO Diagnostic
 *
 * Philosophy: Blocks should use semantic HTML
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Gutenberg_Block_SEO extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-gutenberg-block-seo',
            'title' => 'Gutenberg Block Semantic HTML',
            'description' => 'Review Gutenberg blocks for semantic HTML usage. Custom blocks should use proper heading hierarchy and alt text.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/gutenberg-seo/',
            'training_link' => 'https://wpshadow.com/training/block-editor-seo/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
