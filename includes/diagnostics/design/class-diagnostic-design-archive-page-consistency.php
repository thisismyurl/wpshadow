<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Archive Page Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-archive-page-consistency
 * Training: https://wpshadow.com/training/design-archive-page-consistency
 */
class Diagnostic_Design_ARCHIVE_PAGE_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-archive-page-consistency',
            'title' => __('Archive Page Consistency', 'wpshadow'),
            'description' => __('Verifies archive pages (category, tag, author) styled consistently.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-archive-page-consistency',
            'training_link' => 'https://wpshadow.com/training/design-archive-page-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}