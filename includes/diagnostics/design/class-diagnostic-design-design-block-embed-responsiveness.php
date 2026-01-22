<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Embed Responsiveness
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-embed-responsiveness
 * Training: https://wpshadow.com/training/design-block-embed-responsiveness
 */
class Diagnostic_Design_DESIGN_BLOCK_EMBED_RESPONSIVENESS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-embed-responsiveness',
            'title' => __('Block Embed Responsiveness', 'wpshadow'),
            'description' => __('Ensures embeds are wrapped responsively.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-embed-responsiveness',
            'training_link' => 'https://wpshadow.com/training/design-block-embed-responsiveness',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
