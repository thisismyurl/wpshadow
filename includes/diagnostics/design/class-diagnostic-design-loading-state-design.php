<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Loading State Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-loading-state-design
 * Training: https://wpshadow.com/training/design-loading-state-design
 */
class Diagnostic_Design_LOADING_STATE_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-loading-state-design',
            'title' => __('Loading State Design', 'wpshadow'),
            'description' => __('Confirms loading states use skeleton screens or spinners, communicate progress clearly.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-loading-state-design',
            'training_link' => 'https://wpshadow.com/training/design-loading-state-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
