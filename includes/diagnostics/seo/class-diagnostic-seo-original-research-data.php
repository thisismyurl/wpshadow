<?php
declare(strict_types=1);
/**
 * Original Research Data Diagnostic
 *
 * Philosophy: Original data establishes authority
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Original_Research_Data extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-original-research-data',
            'title' => 'Original Research and Data',
            'description' => 'Publish original research, surveys, or data studies to establish topical authority and earn backlinks.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/original-research/',
            'training_link' => 'https://wpshadow.com/training/thought-leadership/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}
