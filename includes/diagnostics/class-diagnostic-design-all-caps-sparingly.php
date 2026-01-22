<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: All-Caps Typography Used Sparingly
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-all-caps-sparingly
 * Training: https://wpshadow.com/training/design-all-caps-sparingly
 */
class Diagnostic_Design_ALL_CAPS_SPARINGLY {
    public static function check() {
        return [
            'id' => 'design-all-caps-sparingly',
            'title' => __('All-Caps Typography Used Sparingly', 'wpshadow'),
            'description' => __('Verifies all-caps used only for labels/UI.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-all-caps-sparingly',
            'training_link' => 'https://wpshadow.com/training/design-all-caps-sparingly',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
