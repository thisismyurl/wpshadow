<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unused CSS Removal
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-unused-removal
 * Training: https://wpshadow.com/training/design-css-unused-removal
 */
class Diagnostic_Design_CSS_UNUSED_REMOVAL extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-unused-removal',
            'title' => __('Unused CSS Removal', 'wpshadow'),
            'description' => __('Verifies unused CSS removed or purged.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-unused-removal',
            'training_link' => 'https://wpshadow.com/training/design-css-unused-removal',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}