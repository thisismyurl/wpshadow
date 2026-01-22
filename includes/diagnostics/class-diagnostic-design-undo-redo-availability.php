<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Undo/Redo Availability
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-undo-redo-availability
 * Training: https://wpshadow.com/training/design-undo-redo-availability
 */
class Diagnostic_Design_UNDO_REDO_AVAILABILITY {
    public static function check() {
        return [
            'id' => 'design-undo-redo-availability',
            'title' => __('Undo/Redo Availability', 'wpshadow'),
            'description' => __('Verifies destructive actions provide undo or warning.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-undo-redo-availability',
            'training_link' => 'https://wpshadow.com/training/design-undo-redo-availability',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
