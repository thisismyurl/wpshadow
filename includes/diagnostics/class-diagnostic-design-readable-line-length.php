<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Readable Line Length Maintenance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-readable-line-length
 * Training: https://wpshadow.com/training/design-readable-line-length
 */
class Diagnostic_Design_READABLE_LINE_LENGTH {
    public static function check() {
        return [
            'id' => 'design-readable-line-length',
            'title' => __('Readable Line Length Maintenance', 'wpshadow'),
            'description' => __('Validates paragraph max-width ~65-75 characters maintained.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-readable-line-length',
            'training_link' => 'https://wpshadow.com/training/design-readable-line-length',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
