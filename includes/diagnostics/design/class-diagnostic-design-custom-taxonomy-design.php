<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Custom Taxonomy Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-custom-taxonomy-design
 * Training: https://wpshadow.com/training/design-custom-taxonomy-design
 */
class Diagnostic_Design_CUSTOM_TAXONOMY_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-custom-taxonomy-design',
            'title' => __('Custom Taxonomy Design', 'wpshadow'),
            'description' => __('Checks custom taxonomies display correctly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-custom-taxonomy-design',
            'training_link' => 'https://wpshadow.com/training/design-custom-taxonomy-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}