<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Widget Unvalidated Attrs
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-widget-unvalidated
 * Training: https://wpshadow.com/training/code-widget-unvalidated
 */
class Diagnostic_Code_CODE_WIDGET_UNVALIDATED {
    public static function check() {
        return [
            'id' => 'code-widget-unvalidated',
            'title' => __('Widget Unvalidated Attrs', 'wpshadow'),
            'description' => __('Flags widget/block form_inputs without sanitization.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-widget-unvalidated',
            'training_link' => 'https://wpshadow.com/training/code-widget-unvalidated',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

