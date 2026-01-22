<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Font File Sizes
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-font-file-sizes
 * Training: https://wpshadow.com/training/design-font-file-sizes
 */
class Diagnostic_Design_FONT_FILE_SIZES {
    public static function check() {
        return [
            'id' => 'design-font-file-sizes',
            'title' => __('Font File Sizes', 'wpshadow'),
            'description' => __('Checks font files under 100KB each.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-file-sizes',
            'training_link' => 'https://wpshadow.com/training/design-font-file-sizes',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
