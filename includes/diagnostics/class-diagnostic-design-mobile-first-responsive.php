<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mobile-First Responsive Approach
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-first-responsive
 * Training: https://wpshadow.com/training/design-mobile-first-responsive
 */
class Diagnostic_Design_MOBILE_FIRST_RESPONSIVE {
    public static function check() {
        return [
            'id' => 'design-mobile-first-responsive',
            'title' => __('Mobile-First Responsive Approach', 'wpshadow'),
            'description' => __('Verifies design starts with mobile, progressively enhances.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-first-responsive',
            'training_link' => 'https://wpshadow.com/training/design-mobile-first-responsive',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
