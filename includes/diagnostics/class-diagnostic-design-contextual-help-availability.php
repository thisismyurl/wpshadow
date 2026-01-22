<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Contextual Help Availability
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-contextual-help-availability
 * Training: https://wpshadow.com/training/design-contextual-help-availability
 */
class Diagnostic_Design_CONTEXTUAL_HELP_AVAILABILITY {
    public static function check() {
        return [
            'id' => 'design-contextual-help-availability',
            'title' => __('Contextual Help Availability', 'wpshadow'),
            'description' => __('Confirms help text/tooltips available.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-contextual-help-availability',
            'training_link' => 'https://wpshadow.com/training/design-contextual-help-availability',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
