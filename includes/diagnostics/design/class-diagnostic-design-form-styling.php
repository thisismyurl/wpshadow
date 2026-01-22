<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WordPress Form Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-form-styling
 * Training: https://wpshadow.com/training/design-form-styling
 */
class Diagnostic_Design_FORM_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-styling',
            'title' => __('WordPress Form Styling', 'wpshadow'),
            'description' => __('Checks search forms, comment forms styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-styling',
            'training_link' => 'https://wpshadow.com/training/design-form-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
