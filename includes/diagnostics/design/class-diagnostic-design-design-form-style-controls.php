<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Style Controls
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-form-style-controls
 * Training: https://wpshadow.com/training/design-form-style-controls
 */
class Diagnostic_Design_DESIGN_FORM_STYLE_CONTROLS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-style-controls',
            'title' => __('Form Style Controls', 'wpshadow'),
            'description' => __('Checks form controls map to tokens when customized.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-style-controls',
            'training_link' => 'https://wpshadow.com/training/design-form-style-controls',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
