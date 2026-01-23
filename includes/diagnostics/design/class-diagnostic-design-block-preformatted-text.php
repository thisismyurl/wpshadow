<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Preformatted Text Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-preformatted-text
 * Training: https://wpshadow.com/training/design-block-preformatted-text
 */
class Diagnostic_Design_BLOCK_PREFORMATTED_TEXT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-preformatted-text',
            'title' => __('Preformatted Text Styling', 'wpshadow'),
            'description' => __('Validates code/preformatted properly styled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-preformatted-text',
            'training_link' => 'https://wpshadow.com/training/design-block-preformatted-text',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}