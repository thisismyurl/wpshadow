<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hover State Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-hover-state-clarity
 * Training: https://wpshadow.com/training/design-hover-state-clarity
 */
class Diagnostic_Design_HOVER_STATE_CLARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-hover-state-clarity',
            'title' => __('Hover State Clarity', 'wpshadow'),
            'description' => __('Validates hover states show interactivity change.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-hover-state-clarity',
            'training_link' => 'https://wpshadow.com/training/design-hover-state-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}