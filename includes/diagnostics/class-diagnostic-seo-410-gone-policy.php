<?php declare(strict_types=1);
/**
 * 410 Gone Policy Diagnostic
 *
 * Philosophy: Cleanly retire content at scale
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_410_Gone_Policy {
    public static function check() {
        return [
            'id' => 'seo-410-gone-policy',
            'title' => 'Use 410 for Permanently Removed Content',
            'description' => 'Consider returning HTTP 410 (Gone) for permanently removed content to expedite deindexation and clarify intent.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/410-gone-seo/',
            'training_link' => 'https://wpshadow.com/training/http-status-seo/',
            'auto_fixable' => false,
            'threat_level' => 25,
        ];
    }
}
