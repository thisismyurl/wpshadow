<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Relationship Loop Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-relationship-loop-styling
 * Training: https://wpshadow.com/training/design-relationship-loop-styling
 */
class Diagnostic_Design_RELATIONSHIP_LOOP_STYLING {
    public static function check() {
        return [
            'id' => 'design-relationship-loop-styling',
            'title' => __('Relationship Loop Styling', 'wpshadow'),
            'description' => __('Validates ACF relationship loops, queries styled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-relationship-loop-styling',
            'training_link' => 'https://wpshadow.com/training/design-relationship-loop-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
