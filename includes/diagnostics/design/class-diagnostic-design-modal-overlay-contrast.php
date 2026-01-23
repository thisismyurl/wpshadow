<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Modal Overlay Contrast
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-modal-overlay-contrast
 * Training: https://wpshadow.com/training/design-modal-overlay-contrast
 */
class Diagnostic_Design_MODAL_OVERLAY_CONTRAST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-modal-overlay-contrast',
            'title' => __('Modal Overlay Contrast', 'wpshadow'),
            'description' => __('Checks overlay darkness sufficient to make modal stand out (0.5-0.8 opacity).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-modal-overlay-contrast',
            'training_link' => 'https://wpshadow.com/training/design-modal-overlay-contrast',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}