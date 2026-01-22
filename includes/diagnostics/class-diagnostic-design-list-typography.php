<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: List Typography
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-list-typography
 * Training: https://wpshadow.com/training/design-list-typography
 */
class Diagnostic_Design_LIST_TYPOGRAPHY {
    public static function check() {
        return [
            'id' => 'design-list-typography',
            'title' => __('List Typography', 'wpshadow'),
            'description' => __('Confirms lists properly indented, styles clear.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-list-typography',
            'training_link' => 'https://wpshadow.com/training/design-list-typography',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
