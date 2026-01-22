<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Input Group Validation Mobile
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-input-group-mobile
 * Training: https://wpshadow.com/training/design-input-group-mobile
 */
class Diagnostic_Design_INPUT_GROUP_MOBILE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-input-group-mobile',
            'title' => __('Input Group Validation Mobile', 'wpshadow'),
            'description' => __('Confirms form grouping clear.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-input-group-mobile',
            'training_link' => 'https://wpshadow.com/training/design-input-group-mobile',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
