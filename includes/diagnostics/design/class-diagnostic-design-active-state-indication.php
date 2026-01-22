<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Active State Indication
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-active-state-indication
 * Training: https://wpshadow.com/training/design-active-state-indication
 */
class Diagnostic_Design_ACTIVE_STATE_INDICATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-active-state-indication',
            'title' => __('Active State Indication', 'wpshadow'),
            'description' => __('Confirms active/current states clearly indicated.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-active-state-indication',
            'training_link' => 'https://wpshadow.com/training/design-active-state-indication',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
