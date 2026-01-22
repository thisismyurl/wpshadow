<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: List Semantic Structure
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-list-semantic-structure
 * Training: https://wpshadow.com/training/design-list-semantic-structure
 */
class Diagnostic_Design_LIST_SEMANTIC_STRUCTURE {
    public static function check() {
        return [
            'id' => 'design-list-semantic-structure',
            'title' => __('List Semantic Structure', 'wpshadow'),
            'description' => __('Checks unordered lists use <ul>, ordered use <ol>.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-list-semantic-structure',
            'training_link' => 'https://wpshadow.com/training/design-list-semantic-structure',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
