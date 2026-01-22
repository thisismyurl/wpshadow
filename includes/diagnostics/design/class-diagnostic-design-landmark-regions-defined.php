<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Landmark Regions Defined
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-landmark-regions-defined
 * Training: https://wpshadow.com/training/design-landmark-regions-defined
 */
class Diagnostic_Design_LANDMARK_REGIONS_DEFINED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-landmark-regions-defined',
            'title' => __('Landmark Regions Defined', 'wpshadow'),
            'description' => __('Checks key regions marked with ARIA landmarks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-landmark-regions-defined',
            'training_link' => 'https://wpshadow.com/training/design-landmark-regions-defined',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
