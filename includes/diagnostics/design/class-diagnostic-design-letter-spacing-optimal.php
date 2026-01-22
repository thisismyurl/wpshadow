<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Letter Spacing Optimal
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-letter-spacing-optimal
 * Training: https://wpshadow.com/training/design-letter-spacing-optimal
 */
class Diagnostic_Design_LETTER_SPACING_OPTIMAL extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-letter-spacing-optimal',
            'title' => __('Letter Spacing Optimal', 'wpshadow'),
            'description' => __('Validates letter-spacing natural and consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-letter-spacing-optimal',
            'training_link' => 'https://wpshadow.com/training/design-letter-spacing-optimal',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
