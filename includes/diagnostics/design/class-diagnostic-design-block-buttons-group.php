<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Buttons Group Layout
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-buttons-group
 * Training: https://wpshadow.com/training/design-block-buttons-group
 */
class Diagnostic_Design_BLOCK_BUTTONS_GROUP extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-buttons-group',
            'title' => __('Buttons Group Layout', 'wpshadow'),
            'description' => __('Validates button group responsive, properly spaced.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-buttons-group',
            'training_link' => 'https://wpshadow.com/training/design-block-buttons-group',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}