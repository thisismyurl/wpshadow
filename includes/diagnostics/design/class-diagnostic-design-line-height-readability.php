<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Line Height Readability
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-line-height-readability
 * Training: https://wpshadow.com/training/design-line-height-readability
 */
class Diagnostic_Design_LINE_HEIGHT_READABILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-line-height-readability',
            'title' => __('Line Height Readability', 'wpshadow'),
            'description' => __('Confirms body text line-height 1.5-1.6.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-line-height-readability',
            'training_link' => 'https://wpshadow.com/training/design-line-height-readability',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}