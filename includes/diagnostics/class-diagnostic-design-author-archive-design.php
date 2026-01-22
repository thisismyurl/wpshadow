<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Author Archive Design
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-author-archive-design
 * Training: https://wpshadow.com/training/design-author-archive-design
 */
class Diagnostic_Design_AUTHOR_ARCHIVE_DESIGN {
    public static function check() {
        return [
            'id' => 'design-author-archive-design',
            'title' => __('Author Archive Design', 'wpshadow'),
            'description' => __('Checks author pages styled professionally.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-author-archive-design',
            'training_link' => 'https://wpshadow.com/training/design-author-archive-design',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
