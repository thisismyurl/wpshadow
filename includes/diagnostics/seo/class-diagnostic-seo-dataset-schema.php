<?php
declare(strict_types=1);
/**
 * Dataset Schema Diagnostic
 *
 * Philosophy: Dataset schema for data publications
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Dataset_Schema extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-dataset-schema',
            'title' => 'Dataset Schema Markup',
            'description' => 'Add Dataset schema for research data: distribution, temporal coverage, license.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/dataset-schema/',
            'training_link' => 'https://wpshadow.com/training/research-structured-data/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}
