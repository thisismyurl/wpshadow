<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Link Purpose Clear
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-link-purpose-clear
 * Training: https://wpshadow.com/training/design-link-purpose-clear
 */
class Diagnostic_Design_LINK_PURPOSE_CLEAR {
    public static function check() {
        return [
            'id' => 'design-link-purpose-clear',
            'title' => __('Link Purpose Clear', 'wpshadow'),
            'description' => __('Validates link purpose clear from link text alone.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-link-purpose-clear',
            'training_link' => 'https://wpshadow.com/training/design-link-purpose-clear',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
