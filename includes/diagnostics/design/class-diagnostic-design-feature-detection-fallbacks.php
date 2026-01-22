<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Feature Detection Fallbacks
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-feature-detection-fallbacks
 * Training: https://wpshadow.com/training/design-feature-detection-fallbacks
 */
class Diagnostic_Design_FEATURE_DETECTION_FALLBACKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-feature-detection-fallbacks',
            'title' => __('Feature Detection Fallbacks', 'wpshadow'),
            'description' => __('Validates fallbacks for unsupported features.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-feature-detection-fallbacks',
            'training_link' => 'https://wpshadow.com/training/design-feature-detection-fallbacks',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
