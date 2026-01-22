<?php declare(strict_types=1);
/**
 * Robots Meta Audit Diagnostic
 *
 * Philosophy: Correct index/follow settings by template
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Robots_Meta_Audit {
    public static function check() {
        return [
            'id' => 'seo-robots-meta-audit',
            'title' => 'Robots Meta Audit',
            'description' => 'Audit robots meta across templates (archive, search, utility pages) to ensure low-value pages are not indexed.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/robots-meta-audit/',
            'training_link' => 'https://wpshadow.com/training/indexation-controls/',
            'auto_fixable' => false,
            'threat_level' => 40,
        ];
    }
}
