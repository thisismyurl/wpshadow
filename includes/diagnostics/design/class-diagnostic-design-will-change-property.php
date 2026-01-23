<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Will-Change Property Usage
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-will-change-property
 * Training: https://wpshadow.com/training/design-will-change-property
 */
class Diagnostic_Design_WILL_CHANGE_PROPERTY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-will-change-property',
            'title' => __('Will-Change Property Usage', 'wpshadow'),
            'description' => __('Validates will-change used sparingly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-will-change-property',
            'training_link' => 'https://wpshadow.com/training/design-will-change-property',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}