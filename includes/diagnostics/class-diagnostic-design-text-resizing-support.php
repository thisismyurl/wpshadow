<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Text Resizing Support
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-text-resizing-support
 * Training: https://wpshadow.com/training/design-text-resizing-support
 */
class Diagnostic_Design_TEXT_RESIZING_SUPPORT {
    public static function check() {
        return [
            'id' => 'design-text-resizing-support',
            'title' => __('Text Resizing Support', 'wpshadow'),
            'description' => __('Checks text resizable to 200% without loss.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-text-resizing-support',
            'training_link' => 'https://wpshadow.com/training/design-text-resizing-support',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
