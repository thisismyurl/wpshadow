<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Textarea Proportions
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-textarea-proportions
 * Training: https://wpshadow.com/training/design-textarea-proportions
 */
class Diagnostic_Design_TEXTAREA_PROPORTIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-textarea-proportions',
            'title' => __('Textarea Proportions', 'wpshadow'),
            'description' => __('Verifies textareas appropriate height.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-textarea-proportions',
            'training_link' => 'https://wpshadow.com/training/design-textarea-proportions',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
