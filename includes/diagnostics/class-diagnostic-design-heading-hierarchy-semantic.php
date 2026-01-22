<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Heading Hierarchy Semantic
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-heading-hierarchy-semantic
 * Training: https://wpshadow.com/training/design-heading-hierarchy-semantic
 */
class Diagnostic_Design_HEADING_HIERARCHY_SEMANTIC {
    public static function check() {
        return [
            'id' => 'design-heading-hierarchy-semantic',
            'title' => __('Heading Hierarchy Semantic', 'wpshadow'),
            'description' => __('Verifies H1 unique per page, hierarchy sequential.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-heading-hierarchy-semantic',
            'training_link' => 'https://wpshadow.com/training/design-heading-hierarchy-semantic',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
