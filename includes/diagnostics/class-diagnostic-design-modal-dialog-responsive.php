<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Modal Dialog Responsiveness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-modal-dialog-responsive
 * Training: https://wpshadow.com/training/design-modal-dialog-responsive
 */
class Diagnostic_Design_MODAL_DIALOG_RESPONSIVE {
    public static function check() {
        return [
            'id' => 'design-modal-dialog-responsive',
            'title' => __('Modal Dialog Responsiveness', 'wpshadow'),
            'description' => __('Confirms modals full-screen on mobile, centered desktop.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-modal-dialog-responsive',
            'training_link' => 'https://wpshadow.com/training/design-modal-dialog-responsive',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
