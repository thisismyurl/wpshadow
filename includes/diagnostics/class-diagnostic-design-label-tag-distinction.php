<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Label Tag Distinction
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-label-tag-distinction
 * Training: https://wpshadow.com/training/design-label-tag-distinction
 */
class Diagnostic_Design_LABEL_TAG_DISTINCTION {
    public static function check() {
        return [
            'id' => 'design-label-tag-distinction',
            'title' => __('Label Tag Distinction', 'wpshadow'),
            'description' => __('Confirms labels and tags distinct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-label-tag-distinction',
            'training_link' => 'https://wpshadow.com/training/design-label-tag-distinction',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
