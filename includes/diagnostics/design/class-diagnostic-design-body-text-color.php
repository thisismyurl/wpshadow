<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Body Text Color
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-body-text-color
 * Training: https://wpshadow.com/training/design-body-text-color
 */
class Diagnostic_Design_BODY_TEXT_COLOR extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-body-text-color',
            'title' => __('Body Text Color', 'wpshadow'),
            'description' => __('Verifies body text has minimum 4.5:1 contrast.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-body-text-color',
            'training_link' => 'https://wpshadow.com/training/design-body-text-color',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}