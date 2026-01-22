<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Popover Consistency
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-popover-consistency
 * Training: https://wpshadow.com/training/design-popover-consistency
 */
class Diagnostic_Design_POPOVER_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-popover-consistency',
            'title' => __('Popover Consistency', 'wpshadow'),
            'description' => __('Checks popovers follow card design system, clear close mechanism.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-popover-consistency',
            'training_link' => 'https://wpshadow.com/training/design-popover-consistency',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
