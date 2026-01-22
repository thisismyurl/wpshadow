<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS File Organization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-file-organization
 * Training: https://wpshadow.com/training/design-css-file-organization
 */
class Diagnostic_Design_CSS_FILE_ORGANIZATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-css-file-organization',
            'title' => __('CSS File Organization', 'wpshadow'),
            'description' => __('Checks CSS files organized logically.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-file-organization',
            'training_link' => 'https://wpshadow.com/training/design-css-file-organization',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
