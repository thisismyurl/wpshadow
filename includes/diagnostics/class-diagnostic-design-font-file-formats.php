<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Font File Formats
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-file-formats
 * Training: https://wpshadow.com/training/design-font-file-formats
 */
class Diagnostic_Design_FONT_FILE_FORMATS {
    public static function check() {
        return [
            'id' => 'design-font-file-formats',
            'title' => __('Font File Formats', 'wpshadow'),
            'description' => __('Validates fonts in modern formats.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-file-formats',
            'training_link' => 'https://wpshadow.com/training/design-font-file-formats',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
