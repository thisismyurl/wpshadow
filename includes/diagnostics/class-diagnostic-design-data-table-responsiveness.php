<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Data Table Responsiveness
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-data-table-responsiveness
 * Training: https://wpshadow.com/training/design-data-table-responsiveness
 */
class Diagnostic_Design_DATA_TABLE_RESPONSIVENESS {
    public static function check() {
        return [
            'id' => 'design-data-table-responsiveness',
            'title' => __('Data Table Responsiveness', 'wpshadow'),
            'description' => __('Verifies large tables handle responsiveness.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-data-table-responsiveness',
            'training_link' => 'https://wpshadow.com/training/design-data-table-responsiveness',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
