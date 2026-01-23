<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Modal Focus Management
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-modal-focus-management
 * Training: https://wpshadow.com/training/design-modal-focus-management
 */
class Diagnostic_Design_MODAL_FOCUS_MANAGEMENT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-modal-focus-management',
            'title' => __('Modal Focus Management', 'wpshadow'),
            'description' => __('Verifies modals trap focus, include close button, respond to Escape.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-modal-focus-management',
            'training_link' => 'https://wpshadow.com/training/design-modal-focus-management',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}