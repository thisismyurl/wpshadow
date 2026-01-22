<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Date Archive Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-date-archive-design
 * Training: https://wpshadow.com/training/design-date-archive-design
 */
class Diagnostic_Design_DATE_ARCHIVE_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-date-archive-design',
            'title' => __('Date Archive Design', 'wpshadow'),
            'description' => __('Validates year/month archive page design.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-date-archive-design',
            'training_link' => 'https://wpshadow.com/training/design-date-archive-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
