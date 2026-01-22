<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Visual Hierarchy Implementation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-visual-hierarchy-structure
 * Training: https://wpshadow.com/training/design-visual-hierarchy-structure
 */
class Diagnostic_Design_VISUAL_HIERARCHY_STRUCTURE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-visual-hierarchy-structure',
            'title' => __('Visual Hierarchy Implementation', 'wpshadow'),
            'description' => __('Analyzes heading sizes, weight contrasts, spacing patterns for clear information hierarchy.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-visual-hierarchy-structure',
            'training_link' => 'https://wpshadow.com/training/design-visual-hierarchy-structure',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
