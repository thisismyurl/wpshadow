<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Modal Height on Mobile
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-modal-mobile-height
 * Training: https://wpshadow.com/training/design-modal-mobile-height
 */
class Diagnostic_Design_MODAL_MOBILE_HEIGHT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-modal-mobile-height',
            'title' => __('Modal Height on Mobile', 'wpshadow'),
            'description' => __('Validates modals don't exceed viewport.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-modal-mobile-height',
            'training_link' => 'https://wpshadow.com/training/design-modal-mobile-height',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
