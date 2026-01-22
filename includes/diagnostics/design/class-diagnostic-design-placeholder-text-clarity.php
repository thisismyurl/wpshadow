<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Placeholder Text Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-placeholder-text-clarity
 * Training: https://wpshadow.com/training/design-placeholder-text-clarity
 */
class Diagnostic_Design_PLACEHOLDER_TEXT_CLARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-placeholder-text-clarity',
            'title' => __('Placeholder Text Clarity', 'wpshadow'),
            'description' => __('Confirms placeholder text helpful.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-placeholder-text-clarity',
            'training_link' => 'https://wpshadow.com/training/design-placeholder-text-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
