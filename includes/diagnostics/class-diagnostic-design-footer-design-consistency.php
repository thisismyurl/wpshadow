<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Footer Design Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-footer-design-consistency
 * Training: https://wpshadow.com/training/design-footer-design-consistency
 */
class Diagnostic_Design_FOOTER_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-footer-design-consistency',
            'title' => __('Footer Design Consistency', 'wpshadow'),
            'description' => __('Checks footer sections organized logically, links properly grouped.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-footer-design-consistency',
            'training_link' => 'https://wpshadow.com/training/design-footer-design-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
