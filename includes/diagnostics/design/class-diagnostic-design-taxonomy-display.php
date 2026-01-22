<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Taxonomy Display Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-taxonomy-display
 * Training: https://wpshadow.com/training/design-taxonomy-display
 */
class Diagnostic_Design_TAXONOMY_DISPLAY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-taxonomy-display',
            'title' => __('Taxonomy Display Consistency', 'wpshadow'),
            'description' => __('Verifies category/tag pages styled per design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-taxonomy-display',
            'training_link' => 'https://wpshadow.com/training/design-taxonomy-display',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
