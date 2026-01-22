<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sticky Element Mobile Impact
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-sticky-element-mobile-impact
 * Training: https://wpshadow.com/training/design-sticky-element-mobile-impact
 */
class Diagnostic_Design_STICKY_ELEMENT_MOBILE_IMPACT {
    public static function check() {
        return [
            'id' => 'design-sticky-element-mobile-impact',
            'title' => __('Sticky Element Mobile Impact', 'wpshadow'),
            'description' => __('Checks sticky headers/footers don't consume space.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sticky-element-mobile-impact',
            'training_link' => 'https://wpshadow.com/training/design-sticky-element-mobile-impact',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
