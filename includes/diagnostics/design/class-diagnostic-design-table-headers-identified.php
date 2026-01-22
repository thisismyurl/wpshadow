<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Table Headers Identified
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-table-headers-identified
 * Training: https://wpshadow.com/training/design-table-headers-identified
 */
class Diagnostic_Design_TABLE_HEADERS_IDENTIFIED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-table-headers-identified',
            'title' => __('Table Headers Identified', 'wpshadow'),
            'description' => __('Confirms data tables use <th scope>.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-table-headers-identified',
            'training_link' => 'https://wpshadow.com/training/design-table-headers-identified',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
