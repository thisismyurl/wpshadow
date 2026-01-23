<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tag Input Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-tag-input-design
 * Training: https://wpshadow.com/training/design-tag-input-design
 */
class Diagnostic_Design_TAG_INPUT_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-tag-input-design',
            'title' => __('Tag Input Design', 'wpshadow'),
            'description' => __('Validates tag inputs work properly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tag-input-design',
            'training_link' => 'https://wpshadow.com/training/design-tag-input-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}